<?php
$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$user_data=[];
$err_msg=[];
// ログイン処理
session_start();
if (isset($_SESSION['id']) === TRUE) {
    if ($_SESSION['user_name'] !== 'admin') {
       header('Location: goods_list.php');
       exit;
    }

   $id = $_SESSION['id'];
   $user_name = $_SESSION['user_name'];
} else {
   // 非ログインの場合、ログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}

// DB接続
$link=mysqli_connect($host,$user,$passwd,$dbname);
if($link === FALSE){
   print 'DB接続失敗';
   exit;
}

mysqli_set_charset($link,'utf8');


 // ユーザーの表示処理
 if($link!==FALSE){
    $sql='SELECT id, user_name, created_date FROM ec_user_table ' ;
             
    
    if ($result = mysqli_query($link, $sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result)) {
           $user_data[$i]['id'] = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
           $user_data[$i]['user_name'] = htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8');
           $user_data[$i]['created_date'] = htmlspecialchars($row['created_date'], ENT_QUOTES, 'UTF-8');
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
    <title>test</title>
    <style type="text/css">
        table,tr,th,td{
            border:solid 1px;
        }
        img{
            width:25px;
        }
        section{
            margin:0 630px;
        }
        body{
          background-color: #EEEEEE	;
       }
    </style>
</head>
<body>
    <a href="ec_logout.php"><img src="../php25/icon-rainbow/icon_038470_16.png" alt=""></a>
    <a href="goods_manage.php"><img src="../php25/icon-rainbow/人物アイコン.png" alt=""></a>
    <?php  foreach ($err_msg as $err){ ?>
    <li><?php print $err; ?></li>
    <?php } ?>
    
     <section>
       <h1>ユーザー情報</h1>
           <table>
               <tr>
                   <th>ユーザー名</th>
                   <th>登録日時</th>
                   
               </tr>
           
<?php       foreach ($user_data as $user) { ?>
               <tr>
                   <td><?php print $user['user_name']; ?></td>
                   <td><?php print $user['created_date']; ?></td>
                   
               </tr>
<?php    } ?>
          </table>
       
   </section>
</body>
</html>