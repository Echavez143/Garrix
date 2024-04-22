let dom_replay = document.querySelector("#replay");
let dom_score = document.querySelector("#score");
let dom_canvas = document.createElement("canvas");
document.querySelector("#canvas").appendChild(dom_canvas);
let CTX = dom_canvas.getContext("2d");

const W = (dom_canvas.width = 400);
const H = (dom_canvas.height = 400);

let snake,
    food,
    currentHue,
    cells = 20,
    cellSize,
    isGameover = false,
    tail = [],
    score = 0, 
    maxScore = window.localStorage.getItem("maxScore") || 0, 
    particles = [],
    splashingParticleCount = 20,
    cellsCount,
    requestID;

let helpers = {
   Vec: class { 
       constructor(x, y) {
        this.x = x;
        this.y = y;
       }
       add(v){
        this.x += v.x;
        this.y += v.y;
        return this;    
       }
       mult(v){ 
        if(v instanceof helpers.Vec){ 
            this.x *= v.x;
            this.y *= v.y;
            return this;
        }
        else { 
            this.x *= v;
            this.y *= v;
            return this;
        }
       }
   }
}; 

function isCollision(v1, v2){
    return v1.x === v2.x && v1.y === v2.y;
}

let KEY = {
    ArrowUp: false,
    ArrowRight: false,
    ArrowDown: false,
    ArrowLeft: false,

    resetState(){ 
        this.ArrowUp = false;
        this.ArrowRight = false;
        this.ArrowDown = false;
        this.ArrowLeft = false;
    },
    listen(){ 
        addEventListener(
            "keydown",
            (e) => {
                if(e.key === "ArrowUp" && this.ArrowDown) return;
                if(e.key === "ArrowDown" && this.ArrowUp) return;
                if(e.key === "ArrowLeft" && this.ArrowRight) return;
                if(e.key === "ArrowRight" && this.ArrowLeft) return;
                this[e.key] = true;
                Object.keys(this)
                .filter((f) => f !== e.key && f !== "listen" && f !== "resetState") 
                .forEach((k) => {
                    this[k] = false;
                });
                return false; 
            }
        );
    }
}; 

class Snake { 
    constructor (i, type) {
        this.pos = new helpers.Vec(W / 2, H / 2); 
        this.dir = new helpers.Vec(0, 0); 
        this.type = type;
        this.index = i;
        this.delay = 5;
        this.size = W / cells;
        this.color = "white";
        this.history = [];
        this.total = 1;
    }

    draw(){ 
        let {x, y} = this.pos;
        CTX.fillStyle = this.color;
        CTX.shadowBlur = 20;
        CTX.shadowColor = "#F0E68C"; 
        CTX.fillRect(x, y, this.size, this.size); 
        CTX.shadowBlur = 0;
        for (let i = 0; i < this.history.length - 1; i++){ 
            let {x, y} = this.history[i];
            CTX.lineWidth = 1;
            CTX.fillStyle = "rgba(255, 255, 255, 1)";
            CTX.fillRect(x, y, this.size, this.size); 
        }
    }

    walls(){ 
        let {x, y} = this.pos;
        if (x + cellSize > W){
            this.pos.x = 0;
        }
        if (y + cellSize > H){ 
            this.pos.y = 0; 
        }
        if (y < 0){ 
            this.pos.y = H - cellSize; 
        }
        if (x < 0){
            this.pos.x = W - cellSize;
        }
    }

    controls(){ 
        let dir = this.size;
        if (KEY.ArrowUp){
            this.dir = new helpers.Vec(0, -dir);
        }
        if (KEY.ArrowDown){
            this.dir = new helpers.Vec(0, dir); 
        }
        if (KEY.ArrowLeft){
            this.dir = new helpers.Vec(-dir, 0); 
        }
        if (KEY.ArrowRight){
            this.dir = new helpers.Vec(dir, 0);
        }
    }

    selfCollision(){ 
        for(let i = 0; i < this.history.length; i++) { 
            let p = this.history[i];
            if(isCollision(this.pos, p)){ 
                isGameover = true;
            }
        }
    }

    update(){ 
        this.walls();
        this.draw();
        this.controls(); 
        if(isCollision(this.pos, food.pos)){ 
            incrementScore();
            particleSplash();
            food.spawn();
            this.total++;
        }
        this.history[this.total - 1] = new helpers.Vec(this.pos.x, this.pos.y);
        for (let i = 0; i < this.total - 1; i++){
            this.history[i] = this.history[i + 1]; 
        }
        this.pos.add(this.dir);
        this.delay = 5;
        this.total > 3 ? this.selfCollision() : null; 
    }
} 

class Food{ 
    constructor(){
        this.pos = new helpers.Vec(
            ~~(Math.random() * cells) * cellSize,
            ~~(Math.random() * cells) * cellSize
        );
        this.color = currentHue = `hsl(${~~(Math.random() * 360)}, 100%, 50%)`;
        this.size = cellSize; 
    }

    draw() { 
        let {x, y} = this.pos;
        CTX.globalCompositeOperation = "lighten";
        CTX.shadowBlur = 20;
        CTX.shadowColor = this.color;
        CTX.fillStyle = this.color;
        CTX.fillRect(x, y, this.size, this.size); 
        CTX.globalCompositeOperation = "source-over";
        CTX.shadowBlur = 0;
    }

    spawn(){ 
        let randX = ~~(Math.random() * cells) * this.size;
        let randY = ~~(Math.random() * cells) * this.size;
        for (let path of snake.history) {
            if(isCollision(new helpers.Vec(randX, randY), path)) {
                return this.spawn();
            }
        }
        this.color = currentHue = `hsl(${~~(Math.random() * 360)}, 100%, 50%)`;
        this.pos = new helpers.Vec(randX, randY); 
    }
} 

class Particles{ 
    constructor(pos, color, size, vel) {
        this.pos = pos;
        this.color = color;
        this.size = Math.abs(size / 2);
        this.ttl = 0;
        this.gravity = -0.2;
        this.vel = vel;
    }

    draw(){ 
        let {x, y} = this.pos;
        let hsl = this.color
            .split("")
            .filter((l) => l.match(/[^hsl()$% ]/)) 
            .join("")
            .split(",")
            .map((n) => +n);
        let [r, g, b] = helpers.hsl2rgb(hsl[0] / 100, hsl[1] / 100, hsl[2] / 100); 
        CTX.shadowColor = `rgba(${r}, ${g}, ${b}, 1)`; 
        CTX.shadowBlur = 0;
        CTX.globalCompositeOperation = "lighter"; 
        CTX.fillStyle = `rgba(${r}, ${g}, ${b}, 1)`; 
        CTX.fillRect(x, y, this.size, this.size); 
        CTX.globalCompositeOperation = "source-over";
    }

    update(){ 
        this.draw();
        this.size -= 0.3;
        this.ttl += 1;
        this.pos.add(this.vel);
        this.vel.y -= this.gravity;
    }
}

function incrementScore() {
    score++;
    dom_score.innerText = score.toString().padStart(2, "0");
}

function particleSplash(){
    for (let i = 0; i < splashingParticleCount; i++){
        let vel = new helpers.Vec(Math.random() * 6 - 3, Math.random() * 6 - 3);
        let position = new helpers.Vec(food.pos.x, food.pos.y);
        particles.push(new Particles(position, currentHue, food.size, vel)); 
    }
}

function clear(){
    CTX.clearRect(0, 0, W, H);
}

function initialize(){ 
    CTX.imageSmoothingEnabled = false;
    KEY.listen();
    cellsCount = cells * cells;
    cellSize = W / cells;
    snake = new Snake(0, ""); 
    food = new Food();
    dom_replay.addEventListener("click", reset, false);
    loop();
}

function loop() {
    clear();
    if(!isGameover) {
        requestID = setTimeout(loop, 5000 / 60);
        snake.update();
        food.draw();
        for (let p of particles) {
            p.update();
        }
        helpers.garbageCollector();
    } else {
        clear();
        gameOver();
    }
}

function gameOver() {
    maxScore ? null : (maxScore = score);
    score > maxScore ? (maxScore = score) : null;
    window.localStorage.setItem("maxScore", maxScore);
    CTX.fillStyle = "#4cffd7";
    CTX.textAlign = "center";
    CTX.font = "bold 30px Poppins, sans-serif"; 
    CTX.fillText("Game Over ", W / 2, H / 2);
    CTX.font = "15px Poppins, sans-serif"; 
    CTX.fillText(`SCORE: ${score}`, W / 2, H / 2 + 60); 
    CTX.fillText(`MAX SCORE: ${maxScore}`, W / 2, H / 2 + 80); 
}

function reset(){
    dom_score.innerText = "00";
    score = 0; 
    snake = new Snake(0, ""); 
    food.spawn(); 
    KEY.resetState(); 
    isGameover = false;
    clearTimeout(requestID);
    loop();
}

initialize();