Requirements 

Yii 1.1.x
PHP 5.3

Install 

Extract to extensions directory
Add as component to main config
'mailto'=>array('class' =>'application.extensions.mailto.Mailto'),

Usage 

$options = array(
    'cc' => 'cc@theemail.com',
    'bcc' => 'bcc@theemail.com',
    'followupto' => 'followupto@theemail.com',
    'subject' => 'The Subject',
    'newsgroup' => 'newsgroup',
    'class' => 'emailLinkClass',
    'id' => 'emailLinkID',
    'name' => 'emailLinkName',
    'title' => 'emailLinkTitle',
);
 
Yii::app()->mailto->link('sendingto@gmail.com', 'javascript', 'Link Text', $options);