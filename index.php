<?php

// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', 'root');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message_array = array();
$error_message = array();
$clean = array();

session_start();

if( !empty($_POST['btn_submit']) ) {
	
	// 表示名の入力チェック
	if( empty($_POST['view_name']) ) {
		$error_message[] = '表示名を入力してください。';
	} else {
		$clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES);

		// セッションに表示名を保存
		$_SESSION['view_name'] = $clean['view_name'];
	}
	
	// メッセージの入力チェック
	if( empty($_POST['message']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	} else {
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
	}

	if( empty($error_message) ) {
		
		// データベースに接続
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);

		// 接続エラーの確認
		if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
		} else {

		
			$mysqli->set_charset('utf8');
			
			$now_date = date("Y-m-d H:i:s");
			
			// データを登録するSQL作成
			$sql = "INSERT INTO message (view_name, message, post_date) VALUES ( '$clean[view_name]', '$clean[message]', '$now_date')";
			
			// データを登録
			$res = $mysqli->query($sql);
		
			if( $res ) {
				$_SESSION['success_message'] = 'メッセージを書き込みました。';
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}
		
			// データベースの接続を閉じる
			$mysqli->close();
		}

		header('Location: ./');
	}
}

// データベースに接続
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 接続エラーの確認
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {

	$sql = "SELECT view_name,message,post_date FROM message ORDER BY post_date DESC";
	$res = $mysqli->query($sql);

    if( $res ) {
		$message_array = $res->fetch_all(MYSQLI_ASSOC);
    }

    $mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Todolist,todolist">
    <meta name="description" content="チャットを兼ね備えたTodoリストです">
    <title>Todolist</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <input id="menu" type="checkbox"/>
    <label for="menu" class="open"><img id="humburger" src="img/humburger.png" alt="ハンバーガー"></label>
    <label for="menu" class="back"></label>
    <aside>   
        <label for="menu" class="close"></label>
        <nav>
            <div id="app" class="container">
                <h1>
                <!-- <button @click="purge">Purge</button> -->
                My Todos
                <span class="info">({{ remaining.length }}/{{ todos.length }})</span>
            </h1>
            <ul>
                <li v-for="(todo, index) in todos">
                <input type="checkbox" v-model="todo.isDone">
                <span :class="{done: todo.isDone}">{{ todo.title }}</span>
                <span @click="deleteItem(index)" class="command">x</span>
                </li>
                <li v-show="!todos.length">Nothing to do, yay!</li>
            </ul>
            <form @submit.prevent="addItem">
                <input type="text" v-model="newItem">
                <input type="submit" value="Add">
            </form>
            </div>
              
        </nav>
        <!-- <div>Sidefooter</div> -->
    </aside>
    <main>
        <h1>TodoList&Talking</h1>


        <?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
            <p class="success_message"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if( !empty($error_message) ): ?>
            <ul class="error_message">
                <?php foreach( $error_message as $value ): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="view_name">name</label>
                <input id="view_name" type="text" name="view_name" 
                value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
            </div>
            <div>
                <label for="message">message</label>
                <textarea id="message" name="message"></textarea>
            </div>
            <input type="submit" name="btn_submit" value="書き込む"　class="art_add">
        </form>
        <hr>
        <section>
        <?php if( !empty($message_array) ){ ?>
        <?php foreach( $message_array as $value ){ ?>
        <article class="item_list">
            <div class="info">
                <h2><?php echo $value['view_name']; ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
                <p><?php echo nl2br($value['message']); ?></p>
            </div>
        </article>
        <?php } ?>
        <?php } ?>
        </section>
        
    </main>

    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script src="js/main.js"></script>
    
</body>
</html>