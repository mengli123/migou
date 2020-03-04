// // 创建画布
// const canvas = document.createElement("canvas");
// const ctx = canvas.getContext("2d");
// canvas.width = 512;
// canvas.height = 480;
// document.body.appendChild(canvas);
//
// // 初始化加载场景/UI
// let  sceneReady = false;//场景变量
// let  sceneImage = new Image();
// sceneImage.src = 'run.gif
// sceneImage.onload  = () =>
// {
//     sceneReady = true
// }
//
// sceneImage.onload = ctx.drawImage(sceneImage, 0, 0, 150, 150)

// 创建画布
const canvas = document.getElementById("canvas1");
const ctx = canvas.getContext("2d");
canvas.width = document.body.clientWidth ;
canvas.height = document.body.clientHeight;
document.body.appendChild(canvas);

// 初始化加载场景/UI
let  sceneReady = false;//场景变量
let  sceneImage = new Image();

ctx.fillStyle = 'rgba(0, 0, 0, 0.25)';
ctx.fillRect(0, 0,document.body.clientWidth , document.body.clientHeight);

let cat = new Image()
cat.src  = 'http://files.jb51.net/file_images/game/201410/2014102411052610.gif'
cat.onload = ctx.drawImage(cat,ctx.width / 2,50,50,50)

