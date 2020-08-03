<?php

require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
// セッション開始
session_start();
// セッション変数からログイン済みか確認
if (isset($_SESSION['id']) === TRUE) {
   // ログイン済みの場合、ホームページへリダイレクト
   header('Location: goods_list.php');
   exit;
}
// セッション変数からログインエラーフラグを確認
if (isset($_SESSION['login_err_flag']) === TRUE) {
   // ログインエラーフラグ取得
   $login_err_flag = $_SESSION['login_err_flag'];
   // エラー表示は1度だけのため、フラグをFALSEへ変更
   $_SESSION['login_err_flag'] = FALSE;
} else {
   // セッション変数が存在しなければエラーフラグはFALSE
   $login_err_flag = FALSE;
}
// Cookie情報からユーザー名を取得
if (isset($_COOKIE['user_name']) === TRUE) {
   $user_name = $_COOKIE['user_name'];
} else {
   $user_name = '';
}
// 特殊文字をHTMLエンティティに変換
$user_name = h($user_name);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
   <meta charset="UTF-8">
   <title>ログイン</title>
   <style>
       input {
           display: block;
           margin-bottom: 10px;
       }
       body{
          background-color: #66CCCC	;
       }
       .top{
         text-align: center;
         font-size:30px;
       }
       .middle{
          font-size:30px;
          margin:100px 750px;
       }
   </style>
</head>
<body>
   <heder>
    <div class="top">ログインページ</div>
   </header>
   <div class="middle"> 
   <form action="ec_login.php" method="post">
       <label for="user_name">ユーザー名</label>
       <input type="text" id="user_name" name="user_name" value="<?php print $user_name; ?>">
       <label for="password">パスワード</label>
       <input type="password" id="password" name="password" value="">
       <input type="submit" value="ログイン">
   </form>
   <a href="user_register.php">新規ユーザー登録</a>
<?php if ($login_err_flag === TRUE) { ?>
   <p>ユーザー名又はパスワードが違います</p>
<?php } ?>
   </div>
</body>
</html>