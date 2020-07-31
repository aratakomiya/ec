<?php
$host   = 'mysql'; 
$user   = 'root';  
$passwd = 'root';   
$dbname = 'practice';
$goods_data=[];
$message='';
$created_at='';
$update_at='';
$selection=0;
$picture='';
$img_dir='./img/';
$err_msg=[];
$messages=[];

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

$link=mysqli_connect($host,$user,$passwd,$dbname);
if($link === FALSE){
   print 'DB接続失敗';
   exit;
}

mysqli_set_charset($link,'utf8');
$mode = '';
if (isset($_POST['mode']) === TRUE) {
    $mode = $_POST['mode'];
}
$drink_id=0;
if (isset($_POST['drink_id']) === TRUE) {
    $drink_id = $_POST['drink_id'];
}

if ($mode === 'add') {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
         // データの受け取り処理
    $name = '';
    if (isset($_POST['name']) === TRUE) {
        $name = $_POST['name'];
    }
    
    // データのフィルタリング処理
    $name = trim($name); // [       ] => []

    // データのバリデーション（整合性チェック）処理
    if (mb_strlen($name) === 0) {
       $err_msg[]='名前を入力してください';
    }
    if(isset($_POST['selection'])){
        $selection=$_POST['selection'];
    }
    if ($selection !== '1' && $selection !== '2') {
        $err_msg[]='公開か非公開を選択してください。';
    }
    $price=0;
    $price=filter_input(INPUT_POST,'price',FILTER_VALIDATE_REGEXP,["options"=>["regexp"=>"/^[0-9]+$/"]]);
     if (mb_strlen($price) === 0) {
       $err_msg[]='値段を入力方法を間違えているもしくは入力していない';
    }
    

    $stock=0;   
    $stock=filter_input(INPUT_POST,'stock',FILTER_VALIDATE_REGEXP,["options"=>["regexp"=>"/^[0-9]+$/"]]);
     if (mb_strlen($stock) === 0) {
       $err_msg[]='在庫を入力方法を間違えているもしくは入力していない';
    }

        if (count($err_msg) === 0) {
            //  HTTP POST でファイルがアップロードされたか確認
            if (is_uploaded_file($_FILES['up_file']['tmp_name']) === TRUE) {
                $new_img = $_FILES['up_file']['name'];
    
                // 画像の拡張子取得
                $extension = pathinfo($new_img, PATHINFO_EXTENSION);
    
                // 拡張子チェック
                if ($extension === 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                    // ユニークID生成し保存ファイルの名前を変更
                    $new_img = md5(uniqid(mt_rand(), true)) . '.' . $extension;
    
                    // 同名ファイルが存在するか確認
                    if (is_file($img_dir . $new_img) !== TRUE) {
                        // ファイルを移動し保存
                        if (move_uploaded_file($_FILES['up_file']['tmp_name'], $img_dir . $new_img) !== TRUE) {
                            $err_msg[] = 'ファイルアップロードに失敗しました';
                        }
                    // 生成したIDがかぶることは通常ないため、IDの再生成ではなく再アップロードを促すようにした
                    } else {
                        $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                    }
                } else {
                    $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEG又はPNGのみ利用可能です。';
                }
            } else {
                $err_msg[] = 'ファイルを選択してください';
            }
        }

        $created_at=date('Y-m-d H:i:s');
        $update_at=date('Y-m-d H:i:s');
        
        

    }
    
   

   
    if(count($err_msg)===0){
    // drink_idは勝手に入る？
    $info_data=[
        'name'=>$name,
        'price'=>$price,
        'img'=>$new_img,
        'status'=>$selection,
        'created_date'=>$created_at,
        'updated_date'=>$update_at
        
        
        ];
        
    $sql='INSERT INTO ec_info_table (name, price, img,status,created_date,updated_date) VALUES(\'' . implode('\',\'', $info_data) . '\')';
    
    if(mysqli_query($link,$sql)===TRUE){
        
        $item_id = mysqli_insert_id($link);

        $stock_data=[
            'item_id' =>$item_id,
            'stock'=>$stock,
            'created_date'=>$created_at,
            'updated_date'=>$update_at
            ];

        $sql='INSERT INTO ec_stock_table (item_id, stock, created_date,updated_date) VALUES(\'' . implode('\',\'', $stock_data) . '\')';
       
        if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'ec_table: INSERTエラー:' . $sql;
           
            }else{
                $messages[]= '登録完了'; 
            }
    }  
    }  
}else if($mode === 'stock_add'){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //   $stock = filter_input(INPUT_POST,'change_stock',FILTER_VALIDATE_REGEXP,["options"=>["regexp"=>"/^[0-9]+$/"]]);
       if(isset($_POST['change_stock'])){
           $stock=$_POST['change_stock'];
       }
       if (preg_match('/^[0-9]+$/', $stock) === 0) {
        $err_msg[] = '在庫は整数で入力して下さい';
    }
       $update_at=date('Y-m-d H:i:s');
       $item_id=$_POST['item_id'];
       if(count($err_msg)===0){
       $sql="UPDATE ec_stock_table SET stock = $stock  ,updated_date= '$update_at' WHERE item_id = $item_id";
       
       if(mysqli_query($link,$sql)===TRUE){
           
           $sql="UPDATE ec_info_table SET updated_date = '$update_at'  WHERE id = $item_id";
           if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'ec_info_table: UPDATEエラー:' . $sql;
           
            }else{
                $messages[]='変更完了';
            }
       }
       }
    }
    
}else if($mode === 'selection_add'){
    if(isset($_POST['change_selection'])===TRUE){
        $selection=$_POST['change_selection'];
         if ($selection !== '1' && $selection !== '2') {
        $err_msg[]='公開か非公開を選択してください。';
    }
    
       if(count($err_msg)===0){
        $update_at=date('Y-m-d H:i:s');
        $item_id=$_POST['item_id'];
        $sql="UPDATE ec_stock_table SET updated_date= '$update_at' WHERE item_id = $item_id";
        
        if(mysqli_query($link,$sql)===TRUE){
           
           $sql="UPDATE ec_info_table SET updated_date = '$update_at', status= $selection WHERE id = $item_id";
           
           if(mysqli_query($link,$sql)!==TRUE){
                 $err_msg[] = 'ec_info_table: UPDATEエラー:' . $sql;
           
            }else{
                $messages[]= '変更完了';
            }
       }
       }
    }
}else if($mode==='delete_add'){
    $item_id=$_POST['item_id'];
    $sql="DELETE FROM ec_info_table WHERE id = $item_id";
    
    if(mysqli_query($link,$sql)===TRUE){
        $sql="DELETE FROM ec_stock_table WHERE item_id = $item_id";
        if(mysqli_query($link,$sql)!==TRUE){
            $err_msg[]='ec_stock_table : UPDATEエラー：'.$sql;
        }else{
            print '削除完了';
        }
    }
}
 // 商品の変更
 if($link!==FALSE){
    $sql_flag='SELECT' . PHP_EOL
             . 'ec_info_table.id, ' . PHP_EOL
             . 'ec_info_table.img, ' . PHP_EOL
             . 'ec_info_table.name, ' . PHP_EOL
             . 'ec_info_table.price, ' . PHP_EOL
             . 'ec_info_table.status, ' . PHP_EOL
             . 'ec_stock_table.stock ' . PHP_EOL
             . 'FROM ec_info_table ' . PHP_EOL
             . 'JOIN ec_stock_table ' . PHP_EOL
             . 'ON ec_info_table.id=ec_stock_table.item_id';
    
    if ($result = mysqli_query($link, $sql_flag)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result)) {
           $goods_data[$i]['id'] = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['img'] = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['name']       = htmlspecialchars($row['name'],       ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
           $goods_data[$i]['status']      = htmlspecialchars($row['status'],      ENT_QUOTES, 'UTF-8');
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
    <title>goods_manage</title>
    <style type="text/css">
        table,tr,th,td{
            border:solid 1px;
            margin:0 auto;
        }
        img{
            width:25px;
        }
        .top{
            margin-left: 700px;
            margin-right: 700px;
            
        }
        h2{
            margin-left: 700px;
            margin-right: 700px;
        }
        .submit{
            color:red;
        }
        body{
          background-color: #EEEEEE	;
       }
    </style>
</head>
<body>
    <a href="ec_logout.php" ><img src="../php25/icon-rainbow/icon_038490_16.png" alt=""></a>
    <a href="user_manage.php"><img src="../php25/icon-rainbow/人物アイコン.png" alt=""></a>
    <div class="top">
        <?php  foreach ($err_msg as $err){ ?>
        <li><?php print $err; ?></li>
        <?php } ?>
        <?php  foreach ($messages as $message){ ?>
        <li><?php print $message; ?></li>
        <?php } ?>        
        <h1>新規商品を追加</h1>
        <form method="POST" action="" enctype="multipart/form-data">
        名前   ：<input type="text" name="name"/><br>
        値段   ：<input type="text" name="price"/><br>
        在庫数：<input type="text" name="stock"/><br>
        ファイル:<input type="file" name="up_file"><br>
        　　　　　<select name="selection">
                    <option value="">入力チェック用</option>
                    <option value="1">公開</option>
                    <option value="2">非公開</option>
                </select>
                <input type="hidden" name="mode" value="add">
        <input type="submit" value="商品追加" class="submit">
        </form>
    </div>
     <section>
       <h2>商品情報の変更</h2>
       
           <table>
               <tr>
                   <th>商品画像</th>
                   <th>商品名</th>
                   <th>価格</th>
                   <th>在庫数</th>
                   <th>ステータス</th>
                   <th>操作</th>
               </tr>
           
<?php       foreach ($goods_data as $goods) { ?>
               <tr>
                   <td><img src="<?php print $img_dir . $goods['img']; ?>" alt=""></td>
                   <td><?php print $goods['name']; ?></td>
                   <td><?php print number_format($goods['price']); ?>円</td>
                   <td><form method="POST">
                       <input type="text" name="change_stock"value="<?php print $goods['stock'] ?>">個
                       <input type="hidden" name="mode" value="stock_add">
                       <input type="hidden" name="item_id" value="<?php print $goods['id'] ?>">
                       <input type="submit" value="変更"/>
                   </form></td>
                   <td><form method="POST">
                       　<select name="change_selection">
                              <option value="1"<?php if ($goods['status'] === '1') { ?> selected<?php } ?>>公開</option>
                              <option value="2"<?php if ($goods['status'] === '2') { ?> selected<?php } ?>>非公開</option>
                          </select>
                          <input type="hidden" name="mode" value="selection_add">
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
       
   </section>
   
</body>
</html>