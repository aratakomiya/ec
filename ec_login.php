<?php
/*
*  ログイン処理
*
*  セッションの仕組み理解を優先しているため、一部処理はModelへ分離していません
*  また処理はセッション関連の最低限のみ行っており、本来必要な処理も省略しています
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
// リクエストメソッド確認
if (get_request_method() !== 'POST') {
   // POSTでなければログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}
// セッション開始
session_start();
// POST値取得
$user_name  = get_post_data('user_name');  // メールアドレス
$password = get_post_data('password'); // パスワード
// メールアドレスをCookieへ保存
setcookie('user_name', $user_name, time() + 60 * 60 * 24 * 365);
// データベース接続
$link = get_db_connect();
// メールアドレスとパスワードからuser_idを取得するSQL
$sql = 'SELECT id FROM ec_user_table
       WHERE user_name =\'' . $user_name . '\' AND password =\'' . $password . '\'';
       
// SQL実行し登録データを配列で取得
$data = get_as_array($link, $sql);
// データベース切断
close_db_connect($link);
// 登録データを取得できたか確認
if (isset($data[0]['id'])) {
   // セッション変数にuser_idを保存
   $_SESSION['id'] = $data[0]['id'];
   $_SESSION['user_name'] = $user_name;

  if($user_name==='admin'){
     header('Location: goods_manage.php');
     exit;
  }else{
   header('Location: goods_list.php');
   exit;
  }
} else {
   // セッション変数にログインのエラーフラグを保存
   $_SESSION['login_err_flag'] = TRUE;
   // ログインページへリダイレクト
   header('Location: ec_top_login.php');
   exit;
}