<?php
$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$switch=0;
$sum=0;
$img_dir='./img/';
$err_msg=[];
// DB接続
$link=mysqli_connect($host,$user,$passwd,$dbname);
mysqli_set_charset($link,'utf8');
// ログイン処理
session_start();
if (isset($_SESSION['id']) === TRUE) {
    

   $id = $_SESSION['id'];
   $user_name = $_SESSION['user_name'];
} else {
   // 非ログインの場合、ログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}
// データを取得
if(isset($_POST['purchase'])===TRUE){
    $sql='SELECT' . PHP_EOL
             . 'ec_info_table.id, ' . PHP_EOL
             . 'ec_info_table.img, ' . PHP_EOL
             . 'ec_info_table.name, ' . PHP_EOL
             . 'ec_info_table.price, ' . PHP_EOL
             . 'ec_info_table.status, ' . PHP_EOL
             . 'ec_stock_table.stock, ' . PHP_EOL
             . 'ec_cart_table.amount ' . PHP_EOL
             . 'FROM ec_info_table ' . PHP_EOL
             . 'JOIN ec_stock_table ' . PHP_EOL
             . 'ON ec_info_table.id=ec_stock_table.item_id'. PHP_EOL
             . 'JOIN ec_cart_table ' . PHP_EOL
             . 'ON ec_stock_table.item_id=ec_cart_table.item_id'. PHP_EOL
             . "AND ec_cart_table.user_id = $id";

    if ($result = mysqli_query($link, $sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result)) {
           $goods_data[$i]['id'] = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['img'] = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['name']       = htmlspecialchars($row['name'],       ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['amount']      = htmlspecialchars($row['amount'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['status']      = htmlspecialchars($row['status'],      ENT_QUOTES, 'UTF-8');
           $money=$goods_data[$i]['price']*$goods_data[$i]['amount'];
           $sum+=$money;
           $i++;
           
       }
     } else {
       $err_msg[] = 'SQL失敗:' . $sql;
     }
    
     mysqli_free_result($result);

    // エラーチェックループ
    foreach ($goods_data as $goods) {
        if (isset($goods['status'])=== TRUE){
        $status=(int)$goods['status'];
        }
        if($status === 2){
        $err_msg[]='非公開です。';
        }
        if($goods['stock']===0){
            $err_msg[]='在庫数が0です。';
        }
        if($goods['stock']<$goods['amount']){
            $err_msg[]='在庫数が足りません。';
        }
    }

    if (count($err_msg) === 0) {
        // トランザクションの開始（自動コミット停止）
        mysqli_autocommit($link, false);
        // 購入処理ループ
        foreach ($goods_data as $goods) {
            // 在庫数量の更新
            $sql="DELETE FROM ec_cart_table WHERE user_id = $id ";
            if(mysqli_query($link,$sql)!==TRUE){
                $err_msg[]='ec_cart_table : DELETEエラー：'.$sql;
            }
            $stock=$goods['stock']-$goods['amount'];
            $sql="UPDATE ec_stock_table SET stock = ".$stock." WHERE item_id = ".$goods['id'] ;
            if(mysqli_query($link,$sql)!==TRUE){
                $err_msg[]='ec_stock_table : UPDATEエラー：'.$sql;
            }
        }

        // トランザクションのコミットorロールバック
        if (count($err_msg) === 0) {
            // commit
            mysqli_commit($link);
            $switch=1;
            
            // カートのクリア
        } else {
            // rollback
            mysqli_rollback($link);
        }
    }
 }
 

?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>test</title>
    <style type="text/css">
        p{
            font-size:30px;
        }
    </style>
    
</head>
<body>
     <?php  foreach ($err_msg as $err){ ?>
    <li><?php print $err; ?></li>
    <?php } ?>
  <a href="ec_logout.php"><img src="../php25/icon-rainbow/icon_038490_16.png" class="pic"></a>
  <a href="goods_list.php">商品リストへ</a>
  <table>
               <tr>
                   <th>商品画像</th>
                   <th>商品名</th>
                   <th>価格</th>
                   <th>数量</th>
               </tr>
           
<?php       foreach ($goods_data as $goods) { ?>
               <tr>
                   <td><img src="<?php print $img_dir . $goods['img']; ?>" alt=""></td>
                   <td><?php print $goods['name']; ?></td>
                   <td><?php print number_format($goods['price']); ?>円</td>
                   <td><?php print number_format($goods['amount']); ?>個</td>
               </tr>
              
<?php    } ?>
          </table>
 　　 <p><?php print '合計金額は'.$sum.'円です'; ?></p>
<?php if($switch===1){ ?>
      <h1>購入手続きに成功しました。</h1>
 　<?php } ?>
</body>
</html>