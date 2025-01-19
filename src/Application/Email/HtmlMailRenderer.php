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

    public function render(MessageBody $data): string
    {
        $document = new DOMDocument();

        // <?DOCTYPE html>
        $document->insertBefore($document->createProcessingInstruction('DOCTYPE', 'html'), $document->firstChild);

        // <html>
        $html = $this->addElement($document, $document, 'html');

        // <head>
        $head = $this->addElement($document, $html, 'head');
        $this->addElement($document, $head, 'meta', ['charset' => 'UTF-8']);
        $this->addElement($document, $head, 'meta', ['name' => 'viewport', 'value' => 'width=device-width, initial-scale=1.0']);
        $this->addElement($document, $head, 'title', [], $data->title);

        // <body>
        $body = $this->addElement($document, $html, 'body');
        $container = $this->addContainer($document, $body);
        $this->addHeader($document, $container);
        $this->addContent($document, $container, $data);
        $this->addFooter($document, $container, $data);

        return $document->saveHTML();
    }

    private function addContainer(DOMDocument $document, DOMNode $body): DOMElement
    {
        $table = $this->addElement($document, $body, 'table', [
            'role' => 'presentation',
            'width' => '100%',
            'cellspacing' => '0',
            'cellpadding' => '0',
            'border' => '0'
        ]);
        $tableRow = $this->addElement($document, $table, 'tr');
        $tableCol = $this->addElement($document, $tableRow, 'td', ['align' => 'center']);
        
        return $this->addElement($document, $tableCol, 'div', ['class' => 'container']);
    }

    private function addHeader(DOMDocument $document, DOMNode $container): DOMElement
    {
        $header = $this->addElement($document, $container, 'div', ['class' => 'header']);

        $this->addElement($document, $header, 'img', [
            'src' => 'https://www.wildeligabremen.de/wp-content/uploads/2023/05/cropped-Logo-mit-Schrift_30-Jahre-Kopie_2-e1683381765583.jpg',
            'alt' => 'Wilde Liga Bremen'
        ]);

        return $header;
    }

    private function addContent(DOMDocument $document, DOMNode $container, MessageBody $data): DOMElement
    {
        $content = $this->addElement($document, $container, 'div', ['class' => 'content']);

        $this->addElement($document, $content, 'h1', [], $data->title);
        $this->addElement($document, $content, 'p', [], $data->content);

        foreach ($data->actions as $label => $link) {
            $this->addElement($document, $content, 'a', ['class' => 'cta-button', 'href' => $link], $label);
        }

        return $content;
    }

    private function addFooter(DOMDocument $document, DOMNode $container, MessageBody $data): DOMElement
    {
        $footer = $this->addElement($document, $container, 'div', ['class' => 'footer']);

        foreach ($data->hints as $hint) {
            $this->addElement($document, $footer, 'p', [], $hint);
        }

        return $footer;
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