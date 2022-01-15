<?php
class Mailer extends MailConfiguration{

    private string $Template;
    private string $Sender;
    private string $Subject;
    private array $Receivers;
    private array $Parameters;
    private Render $Twig;

    public function __construct($Template,$Sender, $Subject, $Receivers, $Parameters){
        $this->Template = $Template;
        $this->Sender = $Sender;
        $this->Subject = $Subject;
        $this->Receivers = $Receivers;
        $this->Parameters = $Parameters;
        $this->Twig = new Render(ABSPATH.'/Templates/Email');
    }

    public static function GetDefaultAddresses( $sender ): bool|string
    {
        $senders = [
            'Security' => MailConfiguration::$SECURITY_SENDER_ADDRESS,
            'NoReply' => MailConfiguration::$NO_REPLY_SENDER_ADDRESS
        ];
        return array_key_exists($sender, $senders) ? $senders[$sender] : false;
    }

    //Decrypt emails before sending
    public function SendEmail(): bool
    {
        $completedTemplate = $this->Twig->GetTemplate($this->Template,$this->Parameters);
        //Does not exit on single error.
        if($completedTemplate != false) {
            foreach ($this->Receivers as $recipient => $nameParts) {
                $FullName = implode(' ', $nameParts);
                $FirstName = $nameParts[0];
                $LastName = $nameParts[1];
                $email = new \SendGrid\Mail\Mail();
                $email->setFrom($this->Sender, explode('@', $this->Sender)[1]);
                $email->setSubject($this->Subject);
                $email->addTo($recipient, $FullName);
                $email->addContent("text/plain", strip_tags($completedTemplate));
                $email->addContent('text/html', $completedTemplate);
                $sendgrid = new \SendGrid(MailConfiguration::$SENDGRID_API_KEY);
                try {
                    $response = $sendgrid->send($email);
                    if ($response->statusCode() != 202) {
                        BaseClass::LogError([
                            'Message' => 'Failed to send out email[' . $response->statusCode() . ']',
                            'Exception' => $response->body()
                        ]);
                    }
                } catch (Exception $e) {
                    BaseClass::LogError([
                        'Message' => 'Failed to send out email',
                        'Exception' => $e->getMessage()
                    ]);
                }

            }
        }else{
            BaseClass::LogError([
                'Message' => 'Email template does not exist',
                'Exception' => 'Template name:'.$this->Template
            ]);
        }
        return true;

    }

}