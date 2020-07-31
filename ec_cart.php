<?php
require_once '../../include/model/function.php';

$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$img_dir='./img/';
$err_msg=[];
$price=0;
$change=0;
$data=[];
$cart_data=[];
$goods_data=[];
$stock=0;
$status=1;
$switch=0;
$sum=0;
$link=mysqli_connect($host,$user,$passwd,$dbname);
mysqli_set_charset($link,'utf8');

session_start();
if (isset($_SESSION['id']) === TRUE) {
    

   $id = $_SESSION['id'];
   $user_name = $_SESSION['user_name'];
} else {
   // 非ログインの場合、ログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $mode=get_post_data('mode');
    if($mode==='create'){
        $item_id = get_post_data('item_id');
        if(preg_match("/^[0-9]+$/", $item_id) !== 1){
             $err_msg[] = 'item_idが数値で指定されていません';
        }
           
        
      if(count($err_msg)===0){
          // user_idとitems_idに一致するカート商品がないかチェックする
           $sql="SELECT * FROM ec_cart_table WHERE user_id =$id AND item_id = $item_id";
        
         if ($result = mysqli_query($link, $sql)) {
             $row = mysqli_fetch_assoc($result);
             
             if (isset($row['id']) === TRUE) {
                 // 見つかった（UPDATE）
                  $update_at=date('Y-m-d H:i:s');
              $sql="UPDATE ec_cart_table SET amount = amount +1, updated_date= '$update_at' WHERE item_id = $item_id AND user_id = $id ";
               if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'ec_cart_table: UPDATEエラー:' . $sql;
               }    
             } else {
                // なかった（INSERT INTO）
                  $created_at=date('Y-m-d H:i:s');
              $update_at=date('Y-m-d H:i:s');
              
              $cart_data=[
                'user_id'=>$id,
                'item_id'=>$item_id,
                'amount'=>1,
                'created_date'=>$created_at,
                'updated_date'=>$update_at
                  ];
            
               $sql_flag='INSERT INTO ec_cart_table (user_id, item_id, amount, created_date,updated_date) VALUES(\'' . implode('\',\'', $cart_data) . '\')';
               if(mysqli_query($link,$sql_flag)!==TRUE){
                     $err_msg[] = 'ec_table: UPDATEエラー:' . $sql_flag;
               
                }
             }
         } else {
             // error
             $err_msg[] = 'ec_cart_table: SELECTエラー:' . $sql;
         }


        //  if ($result = mysqli_query($link, $sql)) {
        //       $i = 0;
        //       while ($row = mysqli_fetch_assoc($result)) {
        //           $data[$i]['user_id'] = htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8');
        //           $data[$i]['item_id'] = htmlspecialchars($row['item_id'], ENT_QUOTES, 'UTF-8');
                   
        //           if($data[$i]['user_id'] == $id && $data[$i]['item_id'] == $item_id){
        //                   $switch=1;
                         
        //         }
        //         $i++;
        //       }
        //      } 
          // あれば数量を＋１する
        //   if($switch===1){
        //       $update_at=date('Y-m-d H:i:s');
        //       $sql="UPDATE ec_cart_table SET amount = amount +1, updated_date= '$update_at' WHERE item_id = $item_id AND user_id = $id ";
        //       if(mysqli_query($link,$sql)!==TRUE){
        //          $err_msg[] = 'ec_cart_table: UPDATEエラー:' . $sql;
           
        //     }
        //   }else{
        //       // なければ新規レコードを追加する
        //       $created_at=date('Y-m-d H:i:s');
        //       $update_at=date('Y-m-d H:i:s');
              
        //       $cart_data=[
        //         'user_id'=>$id,
        //         'item_id'=>$item_id,
        //         'amount'=>1,
        //         'created_date'=>$created_at,
        //         'updated_date'=>$update_at
        //           ];
            
        //       $sql='INSERT INTO ec_cart_table (user_id, item_id, amount, created_date,updated_date) VALUES(\'' . implode('\',\'', $cart_data) . '\')';
        //       if(mysqli_query($link,$sql)!==TRUE){
        //              $err_msg[] = 'ec_table: UPDATEエラー:' . $sql;
               
        //         }
        //   }
         
      }
     
    }else if($mode==='amount_add'){
              $item_id = get_post_data('item_id');
              if(preg_match("/^[0-9]+$/", $item_id) !== 1){
                 $err_msg[] = 'item_idが数値で指定されていません';
              }
              $update_at=date('Y-m-d H:i:s');
              $amount = get_post_data('change_amount');
              if(preg_match("/^[+]?([1-9]\d*)$/", $amount) !== 1){
                  $err_msg[] = 'amountは半角整数で1以上入力して下さい';
                  }
              if(count($err_msg)===0){
              $sql="UPDATE ec_cart_table SET amount = $amount, updated_date= '$update_at' WHERE item_id = $item_id AND user_id = $id ";
               if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'ec_cart_table: UPDATEエラー:' . $sql;
           
               }else{
                   print '変更完了';
               }
              }
    }else if($mode==='delete_add'){
              $item_id = get_post_data('item_id');
              if(preg_match("/^[0-9]+$/", $item_id) !== 1){
                 $err_msg[] = 'item_idが数値で指定されていません';
              }
              if(count($err_msg)===0){
                   $sql="DELETE FROM ec_cart_table WHERE item_id = $item_id AND user_id = $id ";
                   if(mysqli_query($link,$sql)!==TRUE){
                      $err_msg[]='ec_stock_table : UPDATEエラー：'.$sql;
                   }else{
                       print '削除完了';
                   }
                } 
    }


}
 if($link!==FALSE){
    $sql='SELECT' . PHP_EOL
             . 'ec_info_table.id, ' . PHP_EOL
             . 'ec_info_table.img, ' . PHP_EOL
             . 'ec_info_table.name, ' . PHP_EOL
             . 'ec_info_table.price, ' . PHP_EOL
             . 'ec_stock_table.stock, ' . PHP_EOL
             . 'ec_cart_table.amount ' . PHP_EOL
             . 'FROM ec_info_table ' . PHP_EOL
             . 'JOIN ec_stock_table ' . PHP_EOL
             . 'ON ec_info_table.id=ec_stock_table.item_id'. PHP_EOL
             . 'JOIN ec_cart_table ' . PHP_EOL
             . 'ON ec_stock_table.item_id=ec_cart_table.item_id'. PHP_EOL
             . "AND ec_cart_table.user_id=$id".PHP_EOL;
    if ($result = mysqli_query($link, $sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result)) {
           $goods_data[$i]['id'] = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['img'] = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['name']       = htmlspecialchars($row['name'],       ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['amount']      = htmlspecialchars($row['amount'],      ENT_QUOTES, 'UTF-8');
           $money=$goods_data[$i]['price']*$goods_data[$i]['amount'];
           $sum+=$money;
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
    <title>cart</title>
    <style type="text/css">
        table,tr,th,td{
            border:solid 1px;
        }
        p{
            font-size:30px;
        }
        .pic{
            width:25px;
        }
    </style>
</head>
<body> 
   <a href="ec_logout.php"><img src="../php25/icon-rainbow/icon_038490_16.png" class="pic"></a>
　 <a href="goods_list.php">商品リストへ</a>
    <?php  foreach ($err_msg as $err){ ?>
    <li><?php print $err; ?></li>
    <?php } ?>
     <section>
       <h1>カートページ</h1>
       
           <table>
               <tr>
                   <th>商品画像</th>
                   <th>商品名</th>
                   <th>価格</th>
                   <th>数量</th>
                   <th>操作</th>
               </tr>
           
<?php       foreach ($goods_data as $goods) { ?>
               <tr>
                   <td><img src="<?php print $img_dir . $goods['img']; ?>" alt=""></td>
                   <td><?php print $goods['name']; ?></td>
                   <td><?php print number_format($goods['price']); ?>円</td>
                   <td><form method="POST">
                       <input type="text" name="change_amount"value="<?php print $goods['amount'] ?>">個
                       <input type="hidden" name="mode" value="amount_add">
                       <input type="hidden" name="item_id" value="<?php print $goods['id'] ?>">
                       <input type="submit" value="変更"/>
                   </form></td>
                   <td><form method="POST" >
                       <input type="hidden" name="mode" value="delete_add">
                       <input type="hidden" name="item_id" value="<?php print $goods['id'] ?>">
                       <input type="submit" name="delete"  value="削除する"/>
                   </form></td>
               </tr>
              
<?php    } ?>
          </table>
        <p><?php print '合計金額は'.$sum.'円です'; ?></p>
       <form method="POST" action="ec_purchase.php">
           <input type="submit" name="purchase" value="購入する"/>
       </form>
   </section>
</body>
</html>