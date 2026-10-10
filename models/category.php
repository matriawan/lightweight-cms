<?php
// All categories for the category menu and the search drop-down, alphabetical
function getCategories() {
    return getDatabase()->query('SELECT id, name FROM t_category ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
}
