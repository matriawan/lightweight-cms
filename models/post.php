<?php
// Builds the WHERE part for the public post list. Only published posts, never drafts.
// $categoryId 0 means all categories. $keyword is searched in the title and in the content.
function publishedPostCondition($categoryId, $keyword) {
    $where = ["p.status = 'published'"];
    $params = [];

    if ($categoryId > 0) {
        $where[] = 'p.category_id = :category_id';
        $params['category_id'] = (int) $categoryId;
    }
    if ($keyword !== '') {
        // Escape LIKE wildcards so the keyword is searched as plain text
        $params['kw_title'] = $params['kw_content'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword) . '%';
        $where[] = '(p.title LIKE :kw_title OR p.content LIKE :kw_content)';
    }

    return [' WHERE ' . implode(' AND ', $where), $params];
}

function countPublishedPosts($categoryId, $keyword) {
    [$where, $params] = publishedPostCondition($categoryId, $keyword);
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_post p' . $where);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// Newest first by publish date (or creation date when there is none)
function getPublishedPosts($categoryId, $keyword, $limit, $offset) {
    [$where, $params] = publishedPostCondition($categoryId, $keyword);
    $sql = 'SELECT p.id, p.title, p.content, p.excerpt, p.category_id, c.name AS category_name, u.display_name AS author_name,
                   COALESCE(p.published_at, p.created_at) AS shown_at
            FROM t_post p
            JOIN t_user u ON u.id = p.user_id
            LEFT JOIN t_category c ON c.id = p.category_id' . $where . '
            ORDER BY shown_at DESC, p.id DESC
            LIMIT :limit OFFSET :offset';
    $stmt = getDatabase()->prepare($sql);
    foreach ($params as $name => $value) {
        $stmt->bindValue(':' . $name, $value);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Number of posts owned by one user, in every status
function countPostsByUser($userId) {
    $stmt = getDatabase()->prepare('SELECT COUNT(*) FROM t_post WHERE user_id = ?');
    $stmt->execute([(int) $userId]);
    return (int) $stmt->fetchColumn();
}

// Short plain text for the list: the excerpt, or the first 200 characters of the content without tags.
// The result is plain text, so the view must escape it.
function postSnippet($post, $length = 200) {
    $excerpt = trim((string) ($post['excerpt'] ?? ''));
    if ($excerpt !== '') {
        return $excerpt;
    }
    // A space where a block tag was, so the end of one paragraph does not stick to the next
    $content = preg_replace('/<\/?(?:p|br|div|li|ul|ol|h[1-6]|blockquote|pre|tr|td)\b[^>]*>/i', ' ', (string) $post['content']);
    $text = html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
}
