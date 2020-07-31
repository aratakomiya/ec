<?php
$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$img_dir='./img/';
$goods_data=[];
session_start();
if (isset($_SESSION['id']) === TRUE) {
    

   $id = $_SESSION['id'];
   $user_name = $_SESSION['user_name'];
} else {
   // 非ログインの場合、ログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}


$link=mysqli_connect($host,$user,$passwd,$dbname);
mysqli_set_charset($link,'utf8');
    
     if($link!==FALSE){
    $sql='SELECT' . PHP_EOL
             . 'ec_info_table.id, ' . PHP_EOL
             . 'ec_info_table.img, ' . PHP_EOL
             . 'ec_info_table.name, ' . PHP_EOL
             . 'ec_info_table.price, ' . PHP_EOL
             . 'ec_info_table.status, ' . PHP_EOL
             . 'ec_stock_table.stock ' . PHP_EOL
             . 'FROM ec_info_table ' . PHP_EOL
             . 'JOIN ec_stock_table ' . PHP_EOL
             . 'ON ec_info_table.id=ec_stock_table.item_id';
    
    if ($result = mysqli_query($link, $sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result)) {
           $goods_data[$i]['id'] = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['img'] = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['name']       = htmlspecialchars($row['name'],       ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['status']      = htmlspecialchars($row['status'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
           $i++;
       }
     } else {
       $err_msg[] = 'SQL失敗:' . $sql;
     }
     mysqli_free_result($result);
 }
 

     mysqli_close($link);
     
     



?>



<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>goods_list</title>
    <style>
        ul {
            list-style-type: none;
            width: 800px;
        }
        
        li {
            float: left;
            width: 200px;
        }
        
        img {
            height: 100px;
        }
        
        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }
        .pic{
            width:25px;
            height:25px;
        }
        
    </style>
</head>
<body>
 <header>
  <a href="ec_logout.php"><img src="../php25/icon-rainbow/icon_038490_16.png" class="pic"></a>
  <a href="ec_cart.php"><img src="../php25/icon-rainbow/カートのアイコン素材.png" class="pic"></a>
  <h1>購入ページ</h1>
  </header>
     
 
     
                
                    
<div class="goods_list">
    <ul class="clearfix">
    <?php foreach ($goods_data as $goods) { ?>
            <?php if($goods['status']==='1'){ ?>
                <li><form method="POST" action="ec_cart.php">
                    
                
                <div><img src="<?php print $img_dir .$goods['img']; ?>" ></div>
                <div><?php print $goods['name']; ?></div>
                <div><?php print number_format($goods['price']); ?>円</div>
                <?php if($goods['stock']>=1){ ?>
                <input type="hidden" name="item_id" value="<?php print $goods['id']; ?>" />
                <input type="hidden" name="mode" value="create" />
                <div><input type="submit" value="カートに入れる"/></div>
                <?php }else { ?>
                <div>売り切れ！！</div>
                <?php } ?>
                </form> </li>
            <?php } ?>
    <?php } ?>

    </ul>
</div>               
           
      
 
</body>
</html>