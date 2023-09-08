<?php

namespace Omi;

/**
 * Initially from: https://github.com/fifthsegment/Sociosent/blob/master/Data%20Collector/NiceSSH.class.php
 * 
 */
class SSH
{

    // SSH Host
    private $ssh_host = 'myserver.example.com';

    // SSH Port
    private $ssh_port = 22;

    // SSH Server Fingerprint
    private $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    // SSH Username
    private $ssh_auth_user = 'username';

    // SSH Public Key File
    private $ssh_auth_pub = '/home/username/.ssh/id_rsa.pub';

    // SSH Private Key File
    private $ssh_auth_priv = '/home/username/.ssh/id_rsa';

    // SSH Private Key Passphrase (null == no passphrase)
    private $ssh_auth_pass;

    // SSH Connection
    private $connection;
	
	public function setup(string $host, string $user, string $pass = null, string $fingerprint = null, string $public_key_path = null, string $private_key_file = null, int $port = 22)
	{
		$this->ssh_host = $host;
		$this->ssh_port = $port;
		$this->ssh_server_fp = $fingerprint;
		
		$this->ssh_auth_user = $user;
		$this->ssh_auth_pass = $pass;
		$this->ssh_auth_pub = $public_key_path;
		$this->ssh_auth_priv = $private_key_file;
	}
	
    public function connect()
	{
        if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port)))
		    throw new \Exception('Cannot connect to server');

        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
		
		# var_dump($fingerprint, $this);
		
		if (strcmp($this->ssh_server_fp, $fingerprint) !== 0)
		{
			# var_dump($this->ssh_host, $fingerprint);
			throw new \Exception('Unable to verify server identity!');
		}

        if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass))
		{
			qvar_dump($this);
			throw new \Exception('Autentication rejected by server');
		}
    }

    public function exec($cmd)
	{
	    if (!($stream = ssh2_exec($this->connection, $cmd)))
			throw new \Exception('SSH command failed');

        stream_set_blocking($stream, true);

        $data = "";

        while ($buf = fread($stream, 4096)) {
            $data .= $buf;
        }

        fclose($stream);

        return $data;
    }
	
	public function upload(string $local_file, string $remote_file, int $create_mode = 0644)
	{
		return ssh2_scp_send($this->connection, $local_file, $remote_file, $create_mode);
	}

    public function disconnect()
	{
		if ($this->connection !== null)
		{
			$this->exec('echo "EXITING" && exit;');
			$this->connection = null;
		}
    }

    public function __destruct()
	{
	    # $this->disconnect();
    }
}
