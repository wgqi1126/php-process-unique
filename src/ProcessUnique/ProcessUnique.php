<?php
/**
 * User: echo
 * Date: 17/2/17
 * Time: 下午12:40
 */

namespace wgqi1126\ProcessUnique;

use Exception;


class ProcessUnique
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $pid;

    /**
     * ProcessUnique constructor.
     * @param string $name
     * @param string $dir
     * @throws Exception
     */
    public function __construct($name, $dir = null)
    {
        $this->pid = getmypid();
        $this->name = $name;
        $this->dir = $dir === null ? sys_get_temp_dir() . '/wgqi1126-process-unique' : $dir;
        $this->file = $this->dir . '/' . md5($this->name) . '.pid';

        if (!$this->pid) {
            throw new Exception("get current pid failed");
        }

        if (!file_exists($this->dir)) {
            umask(0);
            if (!mkdir($this->dir, 0777, true)) {
                throw new Exception("create process unique dir '{$this->dir}' failed");
            }
        }
    }

    /**
     * check process is running
     * @return bool
     * @throws Exception
     */
    public function exists()
    {
        if (!file_exists($this->file)) {
            return false;
        }
        if (!is_readable($this->file)) {
            throw new Exception("process unique file '{$this->file}' not readable");
        }
        $pid_str = trim(file_get_contents($this->file));

        if (!is_numeric($pid_str)) {
            throw new Exception("process unique pid '{$pid_str}' not numeric");
        }
        $pid = intval($pid_str);
        return $this->pidExists($pid);
    }

    /**
     * save process pid to file
     * @throws Exception
     */
    public function save()
    {
        if (!file_put_contents($this->file, $this->pid)) {
            throw new Exception("save pid to file failed");
        }
    }

    /**
     * remove process file
     * @return bool
     */
    public function remove()
    {
        return file_exists($this->file) && unlink($this->file);
    }

    /**
     * check pid is exists
     * @param $pid int
     * @return bool
     * @throws Exception
     */
    protected function pidExists($pid)
    {
        $os = strtolower(PHP_OS);
        if (strpos($os, 'win') === 0) { // windows
            return intval(explode("\n", trim(preg_replace('%[\r\n]+%', "\n", shell_exec("TASKLIST /FI \"PID eq {$pid}\""))))) > 2;
        } else { // other
            return intval(trim(shell_exec("ps -p {$pid} | wc -l"))) > 1;
        }
    }
}
