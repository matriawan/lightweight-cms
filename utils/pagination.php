<?php
// Untuk query data: LIMIT :limit OFFSET :offset, bind dengan PDO::PARAM_INT.
function getPagination($totalData, $currentPage, $perPage) {
    $totalData = max(0, (int) $totalData);
    $perPage = max(1, (int) $perPage);
    $totalPages = (int) ceil($totalData / $perPage);
    $currentPage = min(max(1, (int) $currentPage), max(1, $totalPages));

    return [
        'current_page' => $totalPages === 0 ? 0 : $currentPage,
        'total_pages' => $totalPages,
        'offset' => ($currentPage - 1) * $perPage,
        'per_page' => $perPage,
        'total_data' => $totalData,
    ];
}

function paginationLink($params, $page, $label) {
    $url = '?' . http_build_query(array_merge($params, ['p' => $page]));
    return '<a href="' . htmlspecialchars($url) . '">' . $label . '</a>';
}

function renderPagination($pagination, $params = []) {
    $current = $pagination['current_page'];
    $total = $pagination['total_pages'];
    unset($params['p']);

    $html = '<div class="pagination">';

    if ($total > 1) {
        $start = max(1, min($current - 2, $total - 4));
        $end = min($total, $start + 4);

        if ($current > 1) {
            $html .= paginationLink($params, 1, 'First');
            $html .= paginationLink($params, $current - 1, '&laquo; Prev');
        } else {
            $html .= '<span class="disabled">First</span>';
            $html .= '<span class="disabled">&laquo; Prev</span>';
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i === $current) {
                $html .= '<span class="active">' . $i . '</span>';
            } else {
                $html .= paginationLink($params, $i, $i);
            }
        }

        if ($current < $total) {
            $html .= paginationLink($params, $current + 1, 'Next &raquo;');
            $html .= paginationLink($params, $total, 'Last');
        } else {
            $html .= '<span class="disabled">Next &raquo;</span>';
            $html .= '<span class="disabled">Last</span>';
        }
    }

    $html .= '<span class="info">Page ' . $current . ' of ' . $total
        . ' (' . $pagination['total_data'] . ')</span>';
    $html .= '</div>';

    return $html;
}
