<?php
// Published singles for the top navigation, in creation order
function getPublishedSingles() {
    return getDatabase()->query("SELECT id, title FROM t_single WHERE status = 'published' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
}
