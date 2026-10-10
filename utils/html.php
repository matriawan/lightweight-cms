<?php
// Tags that may stay in user HTML (for example in a bio)
const ALLOWED_HTML_TAGS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'ul', 'ol', 'li', 'a', 'h3', 'h4', 'blockquote', 'code', 'pre', 'hr'];
// Tags that are removed together with everything inside them
const REMOVED_HTML_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg', 'math', 'form', 'textarea', 'select', 'button', 'title', 'head'];

// Returns safe HTML: only allowed tags, no attributes except a safe href on links
function sanitizeHtml($html) {
    // Plain text without tags: keep the line breaks
    if (!preg_match('/<\/?[a-z!][^>]*>/i', $html)) {
        return nl2br(htmlspecialchars($html));
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();

    $root = $doc->getElementById('root');
    if (!$root) {
        return htmlspecialchars($html);
    }
    cleanHtmlNode($root);

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $doc->saveHTML($child);
    }
    return $result;
}

function cleanHtmlNode($node) {
    // Copy the list first because the loop changes the tree
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child->nodeType === XML_TEXT_NODE) {
            continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            $node->removeChild($child); // comments, processing instructions
            continue;
        }

        $tag = strtolower($child->nodeName);
        if (in_array($tag, REMOVED_HTML_TAGS, true)) {
            $node->removeChild($child);
            continue;
        }

        cleanHtmlNode($child);

        if (!in_array($tag, ALLOWED_HTML_TAGS, true)) {
            // Unknown tag: keep its (already cleaned) content, drop the tag
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        cleanHtmlAttributes($child, $tag);
    }
}

function cleanHtmlAttributes($element, $tag) {
    $href = $tag === 'a' ? trim($element->getAttribute('href')) : '';
    foreach (iterator_to_array($element->attributes) as $attribute) {
        $element->removeAttribute($attribute->nodeName);
    }

    if ($tag === 'a') {
        // Only http, https, and mailto links are safe
        $clean = preg_replace('/[\x00-\x20]+/', '', $href);
        if (preg_match('#^(https?://|mailto:)#i', $clean)) {
            $element->setAttribute('href', $href);
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }
}
