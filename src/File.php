<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

class File extends Element
{
    public const FILE_WITH_LIMIT = '#file-with-limit';
    public const T_UPLOAD_URL = '#file-upload-url';
    public const BUTTON_LABEL = '#file-button-label';

    protected static function add_assets(Document $document): void
    {
        parent::add_assets($document);

        $document->js->add(__DIR__ . '/File.js');
        $document->css->add(__DIR__ . '/File.css');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct('div', $attributes + [

                Element::IS => 'File',

                self::BUTTON_LABEL => 'Choose a file',

                'class' => 'widget-file'

            ]);
    }

    protected function infos()
    {
        $path = $this['value'];
        $details = $this->details($path);
        $preview = $this->preview($path);

        $rc = '';

        if ($preview) {
            $rc .= '<div class="preview">';
            $rc .= $preview;
            $rc .= '</div>';
        }

        if ($details) {
            $rc .= '<ul class="details">';

            foreach ($details as $detail) {
                $rc .= '<li>' . $detail . '</li>';
            }

            $rc .= '</ul>';
        }

        return $rc;
    }

    /**
     * @return string[]
     */
    protected function details(string $path): array
    {
        $file = basename($path);

        if (strlen($file) > 40) {
            $file = substr($file, 0, 16) . '…' . substr($file, -16, 16);
        }

        return [

            '<span title="Path: ' . $path . '">' . $file . '</span>',
            format_size(filesize(DOCUMENT_ROOT . $path))

        ];
    }

    protected function preview($path): string
    {
        return '<a class="download" href="' . $path . '">' . $this->t('download', [], [
                'scope' => [
                    'fileupload',
                    'element'
                ]
            ]) . '</a>';
    }

    protected function render_inner_html(): ?string
    {
        $path = $this['value'];

        $rc = new Text([

                'value' => $this['value'],
                'readonly' => true,
                'name' => $this['name'],
                'class' => 'form-control form-control-inline reminder'

            ])

            . ' <div class="alert alert-danger"></div>'
            . ' <label class="btn btn-secondary trigger"><i class="icon-file"></i> '
            . $this->t($this[self::BUTTON_LABEL], [], [ 'scope' => 'button' ])
            . '<input type="file" /></label>';

        #
        # uploading element
        #

        $rc .= '<div class="uploading">';
        $rc .= '<span class="progress like-input"><span class="position">'
            . '<span class="text">&nbsp;</span></span></span> ';
        $rc .= '<button type="button" class="btn btn-danger cancel">'
            . $this->t('cancel', [], [ 'scope' => 'button' ]) . '</button>';
        $rc .= '</div>';

        #
        # the FILE_WITH_LIMIT tag can be used to add a little text after the element
        # reminding the maximum file size allowed for the upload
        #

        $limit = $this[self::FILE_WITH_LIMIT];

        if ($limit) {
            if ($limit === true) {
                $limit = (int) ini_get('upload_max_filesize') * 1024;
            }

            $limit = format_size($limit * 1024);

            $rc .= PHP_EOL . '<div class="file-size-limit small" style="margin-top: .5em">';
            $rc .= $this->t('The maximum file size must be less than :size.', [ ':size' => $limit ]);
            $rc .= '</div>';
        }

        #
        # infos
        #

        $infos = null;

        if ($path) {
            if (!is_file(DOCUMENT_ROOT . $path)) {
                /** @phpstan-ignore-next-line */
                $this->app->logger->debug("path: $path");

                $infos = '<span class="warn">'
                    . $this->t('The file %file is missing !', [ '%file' => basename($path) ]) . '</span>';
            } else {
                $infos = $this->infos();
            }

            if ($infos) {
                $this->add_class('has-info');
            }
        }

        return $rc . <<<EOT
<div class="infos">$infos</div>
EOT;
    }

    protected function alter_dataset(array $dataset): array
    {
        $limit = $this[self::FILE_WITH_LIMIT] ?: 2 * 1024;

        if ($limit === true) {
            $limit = (int) ini_get('upload_max_filesize') * 1024;
        }

        return parent::alter_dataset($dataset) + [

                'name' => $this['name'],
                'max-file-size' => $limit * 1024

            ];
    }

    protected function render_outer_html(): string
    {
        $upload_url = $this[self::T_UPLOAD_URL];

        if ($upload_url) {
            $this->dataset['upload-url'] = $upload_url;
        }

        return parent::render_outer_html();
    }
}
