<?php

$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

$sql = "CREATE TABLE IF NOT EXISTS tbpost"
." ("
. "id INT AUTO_INCREMENT PRIMARY KEY,"
. "name char(32),"
. "comment TEXT,"
. "password char(64),"
. "date TIMESTAMP"
.");";
$stmt = $pdo->query($sql);

$edit_line=-1;
$comment="";
$user_name="";
$password="";
//クエリ一覧
$get_query="SELECT* FROM tbpost WHERE id=:id";
$get_all_query="SELECT* FROM tbpost";
$edit_query="UPDATE tbpost SET name=:name,comment=:comment,date=:date,password=:password where id=:id";
$new_query="INSERT INTO tbpost(name,comment,date,password) VALUES(:name,:comment,:date,:password)";
$delete_query= 'delete from tbpost where id=:id';
$inc_query="SET @i := 0; UPDATE tbpost SET id = (@i := @i +1);";
//編集時にフォームに初期値セット
$system_mes="";
$stmt=$pdo->prepare($get_query);
if(isset($_POST["edit_index"])&&$_POST["edit_index"]>=0){
    $stmt->bindValue(":id",$_POST["edit_index"],PDO::PARAM_INT);
    if($stmt->execute()){
        $data=$stmt->fetch();
        if($data==""){
            $system_mes="該当する投稿がありません";
        }else if($data["password"]==$_POST["password"]){
            $user_name=$data["name"];
            $comment=$data["comment"];
            $password=$data["password"];
            $edit_line=$_POST["edit_index"];
        }
    }
}
?>
<div id="post_form">
    <h2>【投稿フォーム】</h2>
    <form method="post">
        <p>
            <label for="name">名前：</label>
            <input id="name" type="text" placeHolder="名前" name="name" value=<?=$user_name ?>>
        </p>
        <p>
            <label for="text">コメント：</label>
            <input id="text" type="text" placeHolder="コメント" name="comment" value=<?=$comment ?>>
        </p>
        <p>
            <label for="password">パスワード：</label>
            <input id="password" type="password" placeHolder="パスワード" name="password" value="<?=$password ?>">
        </p>
        <p><input type="hidden" name="edit_line" value=<?=$edit_line ?>></p>
        <p><input type="submit"></p>
        <p></p>
    </form>
</div>
<div id="delete_form">
    <h2>【削除フォーム】</h2>
    <form method="post">
        <p>
            <label for="delete_num">番号：</label>
            <input id="delete_num" type="number"  placeHolder="削除する番号" name="delete_index" min="1">
        </p>
        <p>
            <label for="delete_password">パスワード：</label>
            <input id="delete_password" type="password" placeHolder="パスワード" name="password">
        </p>
        <p><input type="submit" value="削除"></p>
    
    </form>
    
</div>
<div id="edit_form">
    <h2>【編集フォーム】</h2>
    <form method="post">
        <p>
            <label for="edit_num">番号：</label>
            <input id="edit_num" type="number"  placeHolder="編集する番号" name="edit_index" min="1">
        </p>
        <p>
            <label for="edit_password">パスワード：</label>
            <input id="edit_password" type="password" placeHolder="パスワード" name="password">
        </p>
        <p><input type="submit" value="編集"></p>
    
    </form>
    
</div>

<?php

    if(isset($_POST["name"])&&$_POST["name"]!=""&&$_POST["comment"]!=""){
        if(isset($_POST["edit_line"])&&(int)$_POST["edit_line"]>=0){
            //編集モード
            $stmt=$pdo->prepare($edit_query);
            $stmt->bindValue(":id",(int)$_POST["edit_line"],PDO::PARAM_INT);
            $stmt->bindValue(":name",$_POST["name"],PDO::PARAM_STR);
            $stmt->bindValue(":comment",$_POST["comment"],PDO::PARAM_STR);
            $stmt->bindValue(":date",date("Y-m-d-H-i-s"),PDO::PARAM_STR);
            $stmt->bindValue(":password",$_POST["password"],PDO::PARAM_STR);
            if($stmt->execute()){
                $system_mes="編集成功";
            }else{
                $system_mes="編集失敗";
            }
        }else{
            //新規投稿モード
            $stmt=$pdo->prepare($new_query);
            $stmt->bindValue(":name",$_POST["name"],PDO::PARAM_STR);
            $stmt->bindValue(":comment",$_POST["comment"],PDO::PARAM_STR);
            $stmt->bindValue(":date",date("Y-m-d-H-i-s"),PDO::PARAM_STR);
            $stmt->bindValue(":password",$_POST["password"],PDO::PARAM_STR);
            if($stmt->execute()){
                $system_mes="新規投稿成功";
            }else{
                $system_mes="入力情報が不正です";
            }

        }
    }else if(isset($_POST["name"])){
        $system_mes="入力情報に不具合があります";
    }
    
?>
<?php
    if(isset($_POST["delete_index"])&&$_POST["delete_index"]!=""&&$_POST["delete_index"]>0){
        $stmt=$pdo->prepare($get_query);
        $stmt->bindValue(":id",$_POST["delete_index"]);
        if($stmt->execute()){
            $datas=$stmt->fetch();
            if($datas==""){
                $system_mes="該当する投稿がありません";
            }else if($datas["password"]==$_POST["password"]&&$_POST["password"]!=""){
                //削除
                $stmt=$pdo->prepare($delete_query);
                $stmt->bindValue(":id",$_POST["delete_index"]);
                if($stmt->execute()){
                    $system_mes="削除成功";
                }else{
                    $system_mes="削除失敗";
                }
            }else if($datas["password"]==""){
                $system_mes="パスワードが設定されていないので削除できません";
            }else if($_POST["password"]==""||$datas["password"]!=$_POST["password"]){
                $system_mes="パスワードに誤りがあります";
            }
            
        }else{
            $system_mes="該当する投稿がありません";
        }
        $stmt=$pdo->prepare($inc_query);
        if($stmt->execute()){
        }else{
        }
    }else if(isset($_POST["delete_index"])&&$_POST["delete_index"]<=0){
        $system_mes="1以上の数を入力してください";
    }
    if($system_mes!=""){
        echo "<p>------------------------------------------</p>";
        echo "<p>$system_mes</p>";
        
    }
?>


<p>----投稿-----------------------------</p>
<ul style="list-style:none;">
    <?php
    //投稿表示
    $stmt=$pdo->prepare($get_all_query);
    if($stmt->execute()){
        $datas=$stmt->fetchAll();
        echo "<p>全".count($datas)."件</p>";
        if(count($datas)==0){
            echo "<p>投稿はありません</p>";
        }
        foreach($datas as $data){
            echo "<li><p>";
            echo "<span> [".$data["id"]."]</span>";
            echo "<span> ".$data["name"]."</span>";
            echo "<span> ".$data["date"]."</span></p>";
            echo "<p style='font-size:32px; font-weight:1000;'> ".$data["comment"]."</p>";
            echo "</li>";
            
        }
    }else{
        echo "取得失敗<br>";
    }
    ?>
</ul>
