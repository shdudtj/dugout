<?php
// MySQL 연결 테스트
$host = "localhost";
$user = "root";
$pw = "";
$dbname = "dugout";

echo "<h2>MySQL 연결 테스트</h2>";

try {
    // 1. 기본 MySQL 서버 연결 테스트 (데이터베이스 없이)
    echo "1. MySQL 서버 연결 테스트...<br>";
    $conn_test = mysqli_connect($host, $user, $pw);
    
    if ($conn_test) {
        echo "✅ MySQL 서버 연결 성공<br>";
        
        // 2. 데이터베이스 존재 확인
        echo "2. 데이터베이스 존재 확인...<br>";
        $db_check = mysqli_query($conn_test, "SHOW DATABASES LIKE '$dbname'");
        
        if (mysqli_num_rows($db_check) > 0) {
            echo "✅ 데이터베이스 '$dbname' 존재함<br>";
            mysqli_close($conn_test);
            
            // 3. 데이터베이스 포함 연결 테스트
            echo "3. 데이터베이스 연결 테스트...<br>";
            $conn_db = mysqli_connect($host, $user, $pw, $dbname);
            
            if ($conn_db) {
                echo "✅ 데이터베이스 연결 성공<br>";
                
                // 4. user_info 테이블 존재 확인
                echo "4. user_info 테이블 확인...<br>";
                $table_check = mysqli_query($conn_db, "SHOW TABLES LIKE 'user_info'");
                
                if (mysqli_num_rows($table_check) > 0) {
                    echo "✅ user_info 테이블 존재함<br>";
                    
                    // 5. 테이블 구조 확인
                    echo "5. 테이블 구조:<br>";
                    $structure = mysqli_query($conn_db, "DESCRIBE user_info");
                    echo "<table border='1'>";
                    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
                    while ($row = mysqli_fetch_array($structure)) {
                        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "❌ user_info 테이블이 존재하지 않음<br>";
                }
                
                mysqli_close($conn_db);
            } else {
                echo "❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "<br>";
            }
        } else {
            echo "❌ 데이터베이스 '$dbname'이 존재하지 않음<br>";
            echo "해결방법: phpMyAdmin에서 'dugout' 데이터베이스를 생성하세요.<br>";
        }
    } else {
        echo "❌ MySQL 서버 연결 실패: " . mysqli_connect_error() . "<br>";
        echo "해결방법:<br>";
        echo "1. XAMPP에서 MySQL 서비스가 실행 중인지 확인<br>";
        echo "2. phpMyAdmin에서 'backend' 사용자 생성 및 권한 부여<br>";
    }
    
} catch (Exception $e) {
    echo "❌ 연결 중 오류 발생: " . $e->getMessage() . "<br>";
}
?>

<style>
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
