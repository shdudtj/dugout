<?php
/**
 * 페이지네이션 컴포넌트
 * 
 * @param int $currentPage 현재 페이지 번호 (1부터 시작)
 * @param int $totalPages 전체 페이지 수
 * @param string $baseUrl 페이지 링크의 기본 URL (선택사항)
 * @param array $options 추가 옵션 배열
 *   - 'show_info' => true/false : "1 of 6" 형태의 정보 표시 여부
 *   - 'prev_text' => string : 이전 버튼 텍스트 (기본값: '‹')
 *   - 'next_text' => string : 다음 버튼 텍스트 (기본값: '›')
 *   - 'class_prefix' => string : CSS 클래스 접두사 (기본값: 'pagination')
 * 
 * @return string HTML 문자열 반환
 */
function renderPagination($currentPage = 1, $totalPages = 1, $baseUrl = '', $options = []) {
    // 기본 옵션 설정
    $defaults = [
        'show_info' => true,
        'prev_text' => '&lsaquo;',
        'next_text' => '&rsaquo;',
        'class_prefix' => 'pagination'
    ];
    
    $options = array_merge($defaults, $options);
    
    // 유효성 검사
    $currentPage = max(1, intval($currentPage));
    $totalPages = max(1, intval($totalPages));
    $currentPage = min($currentPage, $totalPages);
    
    $html = '<div class="' . $options['class_prefix'] . '">';
    
    // 이전 버튼
    $prevDisabled = ($currentPage <= 1) ? ' disabled' : '';
    $prevPage = max(1, $currentPage - 1);
    $prevUrl = $baseUrl ? $baseUrl . '?page=' . $prevPage : 'javascript:void(0)';
    
    if ($baseUrl && $currentPage > 1) {
        $html .= '<a href="' . $prevUrl . '" class="page-btn' . $prevDisabled . '">' . $options['prev_text'] . '</a>';
    } else {
        $html .= '<button class="page-btn' . $prevDisabled . '" onclick="changePage(' . $prevPage . ')">' . $options['prev_text'] . '</button>';
    }
    
    // 페이지 정보 표시
    if ($options['show_info']) {
        $html .= '<span class="page-indicator">' . $currentPage . ' of ' . $totalPages . '</span>';
    }
    
    // 다음 버튼
    $nextDisabled = ($currentPage >= $totalPages) ? ' disabled' : '';
    $nextPage = min($totalPages, $currentPage + 1);
    $nextUrl = $baseUrl ? $baseUrl . '?page=' . $nextPage : 'javascript:void(0)';
    
    if ($baseUrl && $currentPage < $totalPages) {
        $html .= '<a href="' . $nextUrl . '" class="page-btn' . $nextDisabled . '">' . $options['next_text'] . '</a>';
    } else {
        $html .= '<button class="page-btn' . $nextDisabled . '" onclick="changePage(' . $nextPage . ')">' . $options['next_text'] . '</button>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 숫자 버튼이 있는 페이지네이션 렌더링
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param string $baseUrl 기본 URL
 * @param int $maxButtons 표시할 최대 페이지 버튼 수 (기본값: 5)
 * @return string HTML 문자열
 */
function renderNumberPagination($currentPage = 1, $totalPages = 1, $baseUrl = '', $maxButtons = 5) {
    $currentPage = max(1, intval($currentPage));
    $totalPages = max(1, intval($totalPages));
    $currentPage = min($currentPage, $totalPages);
    
    $html = '<div class="pagination number-pagination">';
    
    // 이전 버튼
    if ($currentPage > 1) {
        $prevUrl = $baseUrl ? $baseUrl . '?page=' . ($currentPage - 1) : 'javascript:void(0)';
        if ($baseUrl) {
            $html .= '<a href="' . $prevUrl . '" class="page-btn">&lsaquo;</a>';
        } else {
            $html .= '<button class="page-btn" onclick="changePage(' . ($currentPage - 1) . ')">&lsaquo;</button>';
        }
    }
    
    // 페이지 번호 버튼들
    $startPage = max(1, $currentPage - floor($maxButtons / 2));
    $endPage = min($totalPages, $startPage + $maxButtons - 1);
    
    // 시작 페이지 조정
    if ($endPage - $startPage + 1 < $maxButtons) {
        $startPage = max(1, $endPage - $maxButtons + 1);
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $currentPage) ? ' active' : '';
        $pageUrl = $baseUrl ? $baseUrl . '?page=' . $i : 'javascript:void(0)';
        
        if ($baseUrl) {
            $html .= '<a href="' . $pageUrl . '" class="page-num' . $activeClass . '">' . $i . '</a>';
        } else {
            $html .= '<button class="page-num' . $activeClass . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
        }
    }
    
    // 다음 버튼
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl ? $baseUrl . '?page=' . ($currentPage + 1) : 'javascript:void(0)';
        if ($baseUrl) {
            $html .= '<a href="' . $nextUrl . '" class="page-btn">&rsaquo;</a>';
        } else {
            $html .= '<button class="page-btn" onclick="changePage(' . ($currentPage + 1) . ')">&rsaquo;</button>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 간단한 페이지네이션 (기존 main.php와 동일한 형태)
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @return string HTML 문자열
 */
function renderSimplePagination($currentPage = 1, $totalPages = 6) {
    return '<div class="pagination">
        <button class="page-btn">&lsaquo;</button>
        <span class="page-indicator">' . $currentPage . ' of ' . $totalPages . '</span>
        <button class="page-btn">&rsaquo;</button>
    </div>';
}
?>
