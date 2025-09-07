// Game State
const game = {
    player: {
        x: 50,
        y: 300,
        width: 32,
        height: 32,
        speed: 5,
        jumpForce: 15,
        isJumping: false,
        velocityY: 0,
        gravity: 0.8
    },
    currentWorld: "past",
    keys: {}
};

// DOM Elements
const playerEl = document.getElementById('player');
const worldPast = document.getElementById('world-past');
const worldFuture = document.getElementById('world-future');
const shiftBtn = document.getElementById('shift-btn');

// Event Listeners
document.addEventListener('keydown', (e) => {
    game.keys[e.key] = true;
    if (e.key === 'q') shiftDimension();
});

document.addEventListener('keyup', (e) => {
    game.keys[e.key] = false;
});

shiftBtn.addEventListener('click', shiftDimension);

// Shift Between Worlds
function shiftDimension() {
    if (game.currentWorld === "past") {
        game.currentWorld = "future";
        worldPast.classList.add('hidden');
        worldFuture.classList.remove('hidden');
    } else {
        game.currentWorld = "past";
        worldFuture.classList.add('hidden');
        worldPast.classList.remove('hidden');
    }
}

// Game Loop
function gameLoop() {
    movePlayer();
    applyGravity();
    updatePlayerPosition();
    requestAnimationFrame(gameLoop);
}

// Player Movement
function movePlayer() {
    if (game.keys['ArrowLeft']) {
        game.player.x -= game.player.speed;
    }
    if (game.keys['ArrowRight']) {
        game.player.x += game.player.speed;
    }
    if (game.keys['ArrowUp'] && !game.player.isJumping) {
        game.player.velocityY = -game.player.jumpForce;
        game.player.isJumping = true;
    }
}

function applyGravity() {
    game.player.velocityY += game.player.gravity;
    game.player.y += game.player.velocityY;

    // Ground collision (simplified)
    if (game.player.y > 300) {
        game.player.y = 300;
        game.player.isJumping = false;
        game.player.velocityY = 0;
    }
}

function updatePlayerPosition() {
    playerEl.style.left = `${game.player.x}px`;
    playerEl.style.top = `${game.player.y}px`;
}

// Start Game
gameLoop();