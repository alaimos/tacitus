<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConvertFileResponse extends BinaryFileResponse
{

    protected $originalSeparator;
    protected $newSeparator;

    /**
     * Constructor.
     *
     * @param \SplFileInfo|string $file               The file to stream
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param bool                $public             Files are public by default
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                $autoEtag           Whether the ETag header should be automatically set
     * @param bool                $autoLastModified   Whether the Last-Modified header should be automatically set
     * @param string              $originalSeparator  Original separator character
     * @param string              $newSeparator       New separator character
     */
    public function __construct($file, $status = 200, $headers = [], $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true,
                                $originalSeparator = "\t", $newSeparator = "\t")
    {
        parent::__construct($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
        $this->originalSeparator = $originalSeparator;
        $this->newSeparator      = $newSeparator;
    }

    /**
     * @param \SplFileInfo|string $file               The file to stream
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param bool                $public             Files are public by default
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                $autoEtag           Whether the ETag header should be automatically set
     * @param bool                $autoLastModified   Whether the Last-Modified header should be automatically set
     * @param string              $originalSeparator  Original separator character
     * @param string              $newSeparator       New separator character
     *
     * @return BinaryFileResponse The created response
     */
    public static function create($file = null, $status = 200, $headers = [], $public = true, $contentDisposition = null, $autoEtag = false,
                                  $autoLastModified = true, $originalSeparator = "\t", $newSeparator = "\t")
    {
        return new static($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified, $originalSeparator, $newSeparator);
    }

    /**
     * Sends the file.
     *
     * {@inheritdoc}
     */
    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            return parent::sendContent();
        }

        if (0 === $this->maxlen) {
            return $this;
        }

        $out  = fopen('php://output', 'wb');
        $file = fopen($this->file->getPathname(), 'rb');

        if ($this->originalSeparator == $this->newSeparator) {
            stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);
        } else {
            while (($data = fgetcsv($file, 0, $this->originalSeparator)) !== false) {
                @fputcsv($out, $data, $this->newSeparator);
            }
        }

        fclose($out);
        fclose($file);

        if ($this->deleteFileAfterSend) {
            unlink($this->file->getPathname());
        }

        return $this;
    }


}
