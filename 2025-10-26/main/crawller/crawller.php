<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KBO 일정 크롤러</title>
    <?php
        // 파라미터 설정 (GET 파라미터 또는 기본값 사용)
        $leId = isset($_GET['leId']) ? $_GET['leId'] : '1';
        $srIdList = isset($_GET['srIdList']) ? $_GET['srIdList'] : '3,4,5,7';
        $seasonId = isset($_GET['seasonId']) ? $_GET['seasonId'] : '2025';
        $gameMonth = isset($_GET['gameMonth']) ? $_GET['gameMonth'] : '10';

        echo "<h2>KBO 일정 크롤링 테스트</h2>";
        echo "<p>파라미터: leId={$leId}, srIdList={$srIdList}, seasonId={$seasonId}, gameMonth={$gameMonth}</p>";

        // KBO 일정 데이터를 가져오는 함수
        function getKBOScheduleData($leId, $srIdList, $seasonId, $gameMonth) {
            $url = "https://www.koreabaseball.com/ws/Schedule.asmx/GetScheduleList";
            
            // 파라미터 데이터 준비
            $postData = array(
                'leId' => $leId,
                'srIdList' => $srIdList,
                'seasonId' => $seasonId,
                'gameMonth' => $gameMonth,
                'teamId' => '',
            );

            echo "<h3>API 호출: {$url}</h3>";
            echo "<p>전송 데이터: " . http_build_query($postData) . "</p>";

            // cURL 초기화
            $ch = curl_init();
            
            // cURL 옵션 설정
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json, text/plain, */*',
                'X-Requested-With: XMLHttpRequest',
                'Referer: https://www.koreabaseball.com/Schedule/Schedule.aspx'
            ));

            // 요청 실행
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            echo "<p><strong>HTTP 상태 코드:</strong> {$httpCode}</p>";

            if ($error) {
                echo "<p><strong>cURL 에러:</strong> {$error}</p>";
                curl_close($ch);
                return null;
            }

            curl_close($ch);

            if ($httpCode == 200 && !empty($response)) {
                echo "<h4>응답 데이터 (처음 1000자):</h4>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";

                // JSON 응답인지 확인
                $jsonData = json_decode($response, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    echo "<h4>JSON 파싱 성공!</h4>";
                    echo "<pre>" . print_r($jsonData, true) . "</pre>";
                    return $jsonData;
                } else {
                    echo "<h4>JSON 파싱 실패 - 원시 응답:</h4>";
                    echo "<pre>" . htmlspecialchars($response) . "</pre>";
                    
                    // XML 응답일 수 있으므로 확인
                    if (strpos($response, '<?xml') === 0) {
                        echo "<h4>XML 응답 감지</h4>";
                        $xml = simplexml_load_string($response);
                        if ($xml !== false) {
                            $jsonFromXml = json_encode($xml);
                            $arrayFromXml = json_decode($jsonFromXml, true);
                            echo "<pre>" . print_r($arrayFromXml, true) . "</pre>";
                            return $arrayFromXml;
                        }
                    }
                }
            } else {
                echo "<p><strong>오류:</strong> HTTP {$httpCode} 또는 응답 없음</p>";
                if (!empty($response)) {
                    echo "<pre>" . htmlspecialchars($response) . "</pre>";
                }
            }

            return null;
        }

        // 크롤링 실행
        $scheduleData = getKBOScheduleData($leId, $srIdList, $seasonId, $gameMonth);
        
        if ($scheduleData) {
            echo "<h3>✅ 크롤링 성공!</h3>";
            echo "<p>데이터를 성공적으로 가져왔습니다.</p>";
        } else {
            echo "<h3>❌ 크롤링 실패</h3>";
            echo "<p>데이터를 가져오지 못했습니다. 파라미터나 엔드포인트를 확인해주세요.</p>";
        }
    ?>
</head>
<body>
    <h1>KBO 일정 크롤러</h1>
    <p>KBO 일정 데이터를 cURL로 가져오는 테스트 모듈입니다.</p>
    
    <form method="GET">
        <h3>파라미터 설정</h3>
        <label>leId: <input type="text" name="leId" value="<?php echo htmlspecialchars($leId); ?>" placeholder="1"></label><br><br>
        <label>srIdList: <input type="text" name="srIdList" value="<?php echo htmlspecialchars($srIdList); ?>" placeholder="3,4,5,7"></label><br><br>
        <label>seasonId: <input type="text" name="seasonId" value="<?php echo htmlspecialchars($seasonId); ?>" placeholder="2025"></label><br><br>
        <label>gameMonth: <input type="text" name="gameMonth" value="<?php echo htmlspecialchars($gameMonth); ?>" placeholder="10"></label><br><br>
        <input type="submit" value="크롤링 실행">
    </form>
    
    <h3>사용 예시</h3>
    <ul>
        <li><a href="?leId=1&srIdList=3,4,5,7&seasonId=2025&gameMonth=10">2025년 10월 일정</a></li>
        <li><a href="?leId=1&srIdList=3,4,5,7&seasonId=2025&gameMonth=9">2025년 9월 일정</a></li>
        <li><a href="?leId=1&srIdList=3,4,5,7&seasonId=2024&gameMonth=10">2024년 10월 일정</a></li>
    </ul>
</body>
</html>