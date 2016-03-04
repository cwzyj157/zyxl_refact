<?php
namespace Org;
class ValidateCodeHelper
{
    public	function getAuthImage($text) {
        $im_x = 68;
        $im_y = 18;
        $im = imagecreatetruecolor($im_x,$im_y);
        $text_c = ImageColorAllocate($im,mt_rand(0,0),mt_rand(55,55),mt_rand(136,136));
        $tmpC0=mt_rand(100,100);
        $tmpC1=mt_rand(220,220);
        $tmpC2=mt_rand(220,220);
        $buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
        imagefill($im,16,13, $buttum_c);

        $font = './Public/Admins/images/code.ttf';


        for ($i=0;$i<strlen($text);$i++){
            $tmp =substr($text,$i,1);
            $array = array(-1,1);
            $p = array_rand($array);
            $an = $array[$p]*mt_rand(1,1);//角度
            $size = 12;
            imagettftext($im, $size, $an, $i*$size,15, $text_c, $font, $tmp);
        }


        $distortion_im = imagecreatetruecolor ($im_x, $im_y);

        imagefill($distortion_im,16,13, $buttum_c);
        for ( $i=0; $i<$im_x; $i++) {
            for ( $j=0; $j<$im_y; $j++) {
                $rgb = imagecolorat($im, $i , $j);
                if( (int)($i+0+sin($j/$im_y*2*M_PI)*5) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*M_PI)*10) >=0 ) {
                    imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*2) , $j , $rgb);
                }
            }
        }
        //加入干扰象素;
        $count = 20;//干扰像素的数量
        for($i=0; $i<$count; $i++){
            $randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
            imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
        }

        $rand = mt_rand(5,30);
        $rand1 = mt_rand(15,25);
        $rand2 = mt_rand(5,10);
        for ($yy=$rand; $yy<=+$rand+2; $yy++){
            for ($px=-10;$px<=10;$px=$px+0.1)
            {
                $x=$px/$rand1;
                if ($x!=0)
                {
                    $y=sin($x);
                }
                $py=$y*$rand2;

                imagesetpixel($distortion_im, $px+0, $py+$yy, $text_c);
            }
        }
        //echo $text;exit;
        //设置文件头;
        Header("Content-type: image/JPEG");
        //以PNG格式将图像输出到浏览器或文件;
        ImagePNG($distortion_im);
        //销毁一图像,释放与image关联的内存;
        ImageDestroy($distortion_im);
        ImageDestroy($im);
    }

    public function make_rand($length="32"){//验证码文字生成函数
        $str="ABCDEFHJKMNPRSTUWXYZ";
        $result="";
        for($i=0;$i<$length;$i++){
            $num[$i]=rand(0,19);
            $result.=$str[$num[$i]];
        }
        return $result;
    }


}