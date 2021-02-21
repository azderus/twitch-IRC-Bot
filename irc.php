<?php
 

include 'config.php';
 

                                 
class IRCBot {
 
        //This is going to hold our TCP/IP connection
        var $socket;
 
        //This is going to hold all of the messages both server and client
        var $ex = array();
 
        /*
        
         Construct item, opens the server connection, logs the bot in
         @param array
 
        */
 
        function __construct($config)
 
        {
 
                //file_put_contents('log.txt', "Starting BOT.");
                //file_put_contents('joins.txt', "");
 
                $this->socket = fsockopen($config['server'], $config['port']);
                $this->login($config);
                $this->main($config);
        }
 
 
 
        /*
 
         Logs the bot in on the server
         @param array
 
        */
 
        function login($config)
        {
                $this->send_data('PASS', $config['pass']);
                $this->send_data('NICK', $config['nick']);
                $this->send_data('USER', $config['nick'].' '.$config['nick'].' '.$config['nick'].' '.$config['nick']);
                $this->join_channel($config['channel']);
        }
 
 
 
        /*
 
         This is the workhorse function, grabs the data from the server and displays on the browser
 
        */
 
        function main($config)
        {             
                $data = fgets($this->socket, 256);
                
                echo nl2br($data);
                
                flush();
 
                //file_put_contents('log.txt', $data, FILE_APPEND);
 
                $this->ex = explode(' ', $data);
                
 
                if(@$this->ex[1] == "JOIN")
                {
                    $parts = explode('!',$this->ex[0]);
                    $nick = ltrim($parts[0], ':');
                    $this->send_data('PRIVMSG '.$config['channel'].' :', 'How ya doing, '.$nick.'?  Make sure to type !logged to log into the database');
                    file_put_contents('joins.txt', time().':'.$nick."\n", FILE_APPEND);
                }
 
 
                if($this->ex[0] == 'PING')
                {
                        $this->send_data('PONG', $this->ex[1]); //Plays ping-pong with the server to stay connected.
                }
                
                $command = str_replace(array(chr(10), chr(13)), '', $this->ex[3]);
                switch($command) //List of commands the bot responds to from a user.
                {    
 
                        case ':!join':      
                                $this->join_channel($this->ex[4]);
                                break;                     
                        case ':!logged':
                                $this->send_data('PRIVMSG '.$config['channel'].' :', 'Selam');
                                break;                            
                }
 
                $this->main($config);
        }
 
 
 
        function send_data($cmd, $msg = null) //displays stuff to the broswer and sends data to the server.
        {
                if($msg == null)
                {
                        fputs($this->socket, $cmd."\r\n");
                        echo $cmd;
                } else {
 
                        fputs($this->socket, $cmd.' '.$msg."\r\n");
                        echo $cmd.' '.$msg;
                }
 
        }
 
 
 
        function join_channel($channel) //Joins a channel, used in the join function.
        {
 
                if(is_array($channel))
                {
                        foreach($channel as $chan)
                        {
                                $this->send_data('JOIN', $chan);
                        }
 
                } else {
                        $this->send_data('JOIN', $channel);
                }
        }     
}
 
//Start the bot
$bot = new IRCBot($config);
?>
