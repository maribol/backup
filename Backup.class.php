<?php

/**
 * Copyright (c) 2013 Samuel Todosiciuc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Maribol Backup Service v 0.1
 * @author      Samuel Todosiciuc <samuel.todosiciuc@gmail.com>
 * @copyright   2013 Samuel Todosiciuc.
 * @link        https://github.com/maribol/
 */
class Backup
{

    private $ftp;
    private $zip;
    private $archiveName;
    private $files = array();
    private $settings = array();

    public function __construct($archiveName, $settings = array())
    {
        $this->zip = new ZipArchive();
        $this->archiveName = $archiveName;
        $this->settings = array_merge(array(
            'recursive' => 1,
            'extensionsExclude' => null,
            'extensionsOnly' => null,
            'ftp' => array(
                'active' => false,
                'host' => null,
                'port' => '21',
                'username' => null,
                'password' => null,
                'path' => null,
                'transferType' => FTP_ASCII, // FTP_ASCII or FTP_BINARY
            )
                ), $settings);
        $this->settings['extensionsExclude'] = $this->settings['extensionsExclude'] != '' ? array_map('strtolower', array_map('trim', explode(',', $this->settings['extensionsExclude']))) : array();
        $this->settings['extensionsOnly'] = $this->settings['extensionsOnly'] != '' ? array_map('strtolower', array_map('trim', explode(',', $this->settings['extensionsOnly']))) : array();
    }

    public function ftpConnect()
    {
        $this->ftp->res = @ftp_connect($this->settings['ftp']['host'], $this->settings['ftp']['port']) or die("Couldn't connect to {$this->settings['ftp']['host']}:{$this->settings['ftp']['port']}");
        if ($this->ftp->res) {
            $this->ftp->login = @ftp_login($this->ftp->res, $this->settings['ftp']['username'], $this->settings['ftp']['password']);
            return true;
        }
        return false;
    }

    public function ftpTransfer($localFile, $remoteFile)
    {
        if (!$this->ftp->login) {
            return false;
        }
        $remoteFile = $this->settings['ftp']['path'] . $remoteFile;
        $this->ftpMkDir($remoteFile);
        $transfer = ftp_put($this->ftp->res, $remoteFile, realpath($localFile), $this->settings['ftp']['transferType']);
        return $transfer;
    }

    private function ftpMkDir($path)
    {
        $dir = explode("/", dirname($path));
        $path = "";
        $ret = true;

        for ($i = 0; $i < count($dir); $i++) {
            $path.= $dir[$i] . '/';
            if (!@ftp_chdir($this->ftp->res, $path)) {
                @ftp_chdir($this->ftp->res, "/");
                if (!@ftp_mkdir($this->ftp->res, $path)) {
                    $ret = false;
                    break;
                } else {
                    @ftp_chmod($this->ftp->res, $path, 777);
                }
            }
        }
        return $ret;
    }

    public function collectFiles($dir = '*')
    {
        $locations = glob($dir);
        foreach ($locations as $location) {
            if (is_dir($location) == 1) {
                if ($this->settings['recursive']) {
                    $this->collectFiles($location . '/*');
                }
            } else {
                $this->files[] = $location;
            }
        }
    }

    public function createZip()
    {
        if ($this->zip->open($this->archiveName, ZipArchive::CREATE) !== TRUE) {
            exit("cannot open $this->archiveName\n");
        }

        foreach ($this->files as $file) {
            $ext = strtolower(end(explode('.', $file)));
            if (count($this->settings['extensionsExclude']) == 0 || !in_array($ext, $this->settings['extensionsExclude'])) {
                if (count($this->settings['extensionsOnly']) == 0 || in_array($ext, $this->settings['extensionsOnly'])) {
                    $this->zip->addFile(realpath($file));
                }
            }
        }
        $res = $this->zip->close();

        if ($this->settings['ftp']['active']) {
            $res = $res && $bk->transferZip();
        }
        return $res;
    }

    public function transferZip()
    {
        $this->ftpConnect();
        return $this->ftpTransfer($this->archiveName, $this->archiveName);
    }

}

