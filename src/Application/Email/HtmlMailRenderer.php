<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlMailRenderer
{
    private array $styles = [
        'body' => [
            'margin' => '0',
            'padding' => '0',
            'font-family' => 'Arial, sans-serif',
        ],
        'table' => [
            'width' => '100%',
            'border-collapse' => 'collapse',
        ],
        'img' => [
            'display' => 'block',
            'max-width' => '100%',
            'height' => 'auto',
        ],
        '.container' => [
            'width' => '100%',
            'max-width' => '600px',
            'margin' => '0 auto',
            'padding' => '20px'
        ],
        '.header' => [
            'text-align' => 'center',
            'padding' => '40px 0',
        ],
        '.content' => [
            'background-color' => '#ffffff',
            'padding' => '20px',
            'text-align' => 'center',
        ],
        '.cta-button' => [
            'background-color' => '#ffc700',
            'color' => '#000000',
            'padding' => '15px 25px',
            'text-decoration' => 'none',
            'font-weight' => 'bold',
            'border-radius' => '5px',
            'display' => 'inline-block',
            'margin-top' => '20px',
        ],
        '.footer' => [
            'text-align' => 'center',
            'padding' => '20px',
            'font-size' => '12px',
            'color' => '#888888'
        ]
    ];

    public function render(array $data): string
    {
        $document = new DOMDocument();
        $html = $this->addElement($document, $document, 'html');

        $this->addHeadElement($document, $html, $data);
        $this->addBodyElement($document, $html, $data);

        return $document->saveHTML();
    }

    private function addHeadElement(DOMDocument $document, DOMNode $parent, array $data): DOMElement
    {
        $head = $this->addElement($document, $parent, 'head');
        
        $this->addElement($document, $head, 'meta', ['charset' => 'UTF-8']);
        $this->addElement($document, $head, 'meta', ['name' => 'viewport', 'value' => 'width=device-width, initial-scale=1.0']);
        $this->addElement($document, $head, 'title', [], $data['subject']);

        return $head;
    }

    private function addBodyElement(DOMDocument $document, DOMNode $parent, array $data): DOMElement
    {
        $body = $this->addElement($document, $parent, 'body');

        // Table + Container
        $table = $this->addElement($document, $body, 'table', [
            'role' => 'presentation',
            'width' => '100%',
            'cellspacing' => '0',
            'cellpadding' => '0',
            'border' => '0'
        ]);
        $tableRow = $this->addElement($document, $table, 'tr');
        $tableCol = $this->addElement($document, $tableRow, 'td', ['align' => 'center']);
        $container = $this->addElement($document, $tableCol, 'div', ['class' => 'container']);

        // Header
        $header = $this->addElement($document, $container, 'div', ['class' => 'header']);
        $this->addElement($document, $header, 'img', [
            'src' => $data['logo']['src'],
            'alt' => $data['logo']['alt']
        ]);

        // Content
        $content = $this->addElement($document, $container, 'div', ['class' => 'content']);
        $this->addElement($document, $content, 'h1', [], $data['subject']);
        $this->addElement($document, $content, 'p', [], $data['content']['text']);
        $this->addElement($document, $content, 'a', [
            'class' => 'cta-button',
            'href' => $data['content']['action']['href']
        ], $data['content']['action']['label']);

        // Footer
        $footer = $this->addElement($document, $container, 'div', ['class' => 'footer']);
        foreach ($data['footer']['hints'] as $hint) {
            $this->addElement($document, $footer, 'p', [], $hint);
        }

        return $body;
    }

    private function addElement(DOMDocument $document, DOMNode $parent, string $tag, array $attributes = [], ?string $text = null): DOMElement
    {
        $element = $document->createElement($tag);
        
        $styles = [];
        $styles = array_merge($styles, $this->styles[$element->tagName] ?? []);

        if (isset($attributes['class'])) {
            $styles = array_merge($styles, $this->styles['.' . $attributes['class']] ?? []);
            unset($attributes['class']);
        }

        foreach ($attributes as $name => $value) {
            $element->setAttribute($name, $value);
        }

        if (count($styles) > 0) {
            $values = [];
            foreach ($styles as $name => $value) {
                $values[] = "$name:$value";
            }
            $element->setAttribute('style', implode(';', $values));
        }

        if ($text !== null) {
            $element->appendChild($document->createTextNode($text));
        }

        $parent->appendChild($element);

        return $element;
    }
}