

const  cat = (catImg,runX,runY,content) => {
    let runx = runX
    let runy = runY
    var inter=50;
    var delt_x=0;
    var delt_y=0;
    var fram_count=0;
    var temp_count=0;
    var crop_width=80;
    var crop_height=96;
    var x_overflow_distance = 300;
    var y_overflow_distance = 300;

    var frameIndex = 0;

    var direction = 0;
    function img(runx,runy,index){
        setInterval(function () {
            //再检测边界参数
            //清除之前的图片墨迹
            if(temp_count!=fram_count){
                runx=runx+delt_x
                runy = runy+delt_y
                temp_count=temp_count+1
            }else{
                delt_x=0
                delt_y = 0
                fram_count=0
                temp_count=0
                direction = 0
            }
            content.clearRect(0,0,canvas.width,canvas.height);
            // 裁剪图片

            content.drawImage(catImg,crop_width*frameIndex,crop_height*direction,crop_width,crop_height,runx,runy,crop_width*2.5,crop_height*2.5);

            frameIndex++;

            frameIndex %=4; //取余，重复0-3这四幅图像

        },100);  // 1帧图像/100s

    }
    img(40,40,1)
}
