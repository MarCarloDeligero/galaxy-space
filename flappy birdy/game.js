const canvas = document.getElementById("game-canvas");
const ctx = canvas.getContext("2d");

// Set canvas dimensions
canvas.width = 800;
canvas.height = 600;

// Game variables
let player = { x: 375, y: 500, width: 50, height: 50, speed: 5 };
let bullets = [];
let enemies = [];
let score = 0;
let gameOver = false;
let bossSpawned = false;

// Key press handling
const keys = {};

document.addEventListener("keydown", (e) => {
  keys[e.key] = true;
});

document.addEventListener("keyup", (e) => {
  keys[e.key] = false;
  if (e.key === "r" && gameOver) restartGame();
});

// Player movement
function movePlayer() {
  if (keys["ArrowLeft"] && player.x > 0) player.x -= player.speed;
  if (keys["ArrowRight"] && player.x < canvas.width - player.width) player.x += player.speed;
  if (keys["ArrowUp"] && player.y > 0) player.y -= player.speed;
  if (keys["ArrowDown"] && player.y < canvas.height - player.height) player.y += player.speed;
}

// Bullet creation and movement
function shootBullet() {
  if (keys[" "]) {
    bullets.push({ x: player.x + player.width / 2 - 2.5, y: player.y, width: 5, height: 10, speed: 7 });
  }
}

function moveBullets() {
  bullets.forEach((bullet, index) => {
    bullet.y -= bullet.speed;
    if (bullet.y < 0) bullets.splice(index, 1);
  });
}

// Enemy creation and movement
function spawnEnemy() {
  const size = random(30, 50);
  enemies.push({
    x: random(0, canvas.width - size),
    y: -size,
    width: size,
    height: size,
    speed: random(2, 4),
  });
}

function moveEnemies() {
  enemies.forEach((enemy, index) => {
    enemy.y += enemy.speed;
    if (enemy.y > canvas.height) enemies.splice(index, 1);
  });
}

// Collision detection
function checkCollisions() {
  // Bullet-enemy collision
  bullets.forEach((bullet, bIndex) => {
    enemies.forEach((enemy, eIndex) => {
      if (
        bullet.x < enemy.x + enemy.width &&
        bullet.x + bullet.width > enemy.x &&
        bullet.y < enemy.y + enemy.height &&
        bullet.y + bullet.height > enemy.y
      ) {
        bullets.splice(bIndex, 1);
        enemies.splice(eIndex, 1);
        score += 10;
      }
    });
  });

  // Enemy-player collision
  enemies.forEach((enemy) => {
    if (
      player.x < enemy.x + enemy.width &&
      player.x + player.width > enemy.x &&
      player.y < enemy.y + enemy.height &&
      player.y + player.height > enemy.y
    ) {
      endGame();
    }
  });
}

// Boss logic
function spawnBoss() {
  if (!bossSpawned && score >= 100) {
    bossSpawned = true;
    enemies.push({ x: 300, y: -100, width: 100, height: 100, speed: 2, health: 10 });
  }
}

function moveBoss() {
  enemies.forEach((boss, index) => {
    if (boss.health) {
      boss.y += boss.speed;
      if (boss.y > 100) boss.y = 100; // Stop at a certain point
    } else {
      enemies.splice(index, 1);
      score += 50;
    }
  });
}

// Utility functions
function random(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function drawRect(obj, color) {
  ctx.fillStyle = color;
  ctx.fillRect(obj.x, obj.y, obj.width, obj.height);
}

function drawScore() {
  document.getElementById("score").textContent = score;
}

function endGame() {
  gameOver = true;
  document.getElementById("game-over").classList.remove("hidden");
}

function restartGame() {
  player = { x: 375, y: 500, width: 50, height: 50, speed: 5 };
  bullets = [];
  enemies = [];
  score = 0;
  gameOver = false;
  bossSpawned = false;
  document.getElementById("game-over").classList.add("hidden");
}

// Game loop
function gameLoop() {
  if (gameOver) return;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // Update game objects
  movePlayer();
  shootBullet();
  moveBullets();
  moveEnemies();
  moveBoss();
  checkCollisions();
  spawnEnemy();
  spawnBoss();

  // Draw game objects
  drawRect(player, BLUE);
  bullets.forEach((bullet) => drawRect(bullet, WHITE));
  enemies.forEach((enemy) => drawRect(enemy, RED));

  // Draw score
  drawScore();

  requestAnimationFrame(gameLoop);
}

// Start the game
setInterval(spawnEnemy, 1000); // Spawn an enemy every second
gameLoop();