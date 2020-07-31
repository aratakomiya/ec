<?php
$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$err_msg=[];
$user_data=[];
$data=[];
// DB接続
$link=mysqli_connect($host,$user,$passwd,$dbname);
if($link === FALSE){
   print 'DB接続失敗';
   exit;
}

mysqli_set_charset($link,'utf8');
// ユーザー登録処理
if (isset($_POST["sign_up"])) {
    
    if (empty($_POST["user_name"])) { 
        $err_msg[] = 'ユーザー名が未入力です。';
    } else if (empty($_POST["password"])) {
        $err_msg[] = 'パスワードが未入力です。';
    } 

$user_name='';
 if(isset($_POST['user_name'])){
           $user_name=$_POST['user_name'];
       }
 if (preg_match('/\A[a-z\d]{6,100}+\z/i', $user_name) === 0) {
        $err_msg[] = '半角英数字かつ文字数は6文字以上で入力してください。';
       }
$password='';
 if(isset($_POST['password'])){
           $password=$_POST['password'];
       }
 if (preg_match('/\A[a-z\d]{6,100}+\z/i', $password) === 0) {
        $err_msg[] = '半角英数字かつ文字数は6文字以上で入力してください。';
       }
// 名前の重なりチェック
 if(count($err_msg)===0){
        $sql='SELECT * FROM ec_user_table';
        
         if ($result = mysqli_query($link, $sql)) {
               $i = 0;
               while ($row = mysqli_fetch_assoc($result)) {
                   $user_data[$i]['user_name'] = htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8');
                   
                   if($user_data[$i]['user_name'] == $user_name){
                          $err_msg[] = 'ご希望のユーザー名は既に使用されています。';
                         
                }
                $i++;
               }
             } else {
               $err_msg[] = 'SQL失敗:' . $sql;
             }
             mysqli_free_result($result);
    }
// 　ユーザー追加
 if(count($err_msg)===0){
     $created_at=date('Y-m-d H:i:s');
     $update_at=date('Y-m-d H:i:s');
     $data=[
        'user_name'=>$user_name,
        'password'=>$password,
        'created_date'=>$created_at,
        'updated_date'=>$update_at
        
        
        ];
        
    $sql='INSERT INTO ec_user_table (user_name, password,created_date,updated_date) VALUES(\'' . implode('\',\'', $data) . '\')';
    if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'point_customer_table: UPDATEエラー:' . $sql;
           
            }else{
                print "登録完了"; 
            }
 }
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
        input {
           display: block;
           margin-bottom: 10px;
       }
       body{
          background-color: #66CCCC	;
       }
       .middle{
        margin-left: 700px;
        margin-right: 700px;
       }
       img{
           width:25px;
       }
    </style>
</head>
<body>
　　　<a href="ec_top_login.php"><img src="../php25/icon-rainbow/icon_038470_16.png" ></a>
   
    <div class="middle">
        <?php  foreach ($err_msg as $err){ ?>
        <li><?php print $err; ?></li>
        <?php } ?>
        <h1>ユーザー登録</h1>
        <form method="POST" action="" >
        ユーザー名<input type="text" name="user_name"/><br>
        パスワード<input type="text" name="password"/><br>
        <input type="submit" name="sign_up" value="登録">
        </form>
    </div>
</body>
</html>