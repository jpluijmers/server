<?php 
/**
 * @package plugins.youTubeDistribution
 * @subpackage admin
 */
class Form_YouTubeProfileConfiguration extends Form_ProviderProfileConfiguration
{
	public function getObject($objectType, array $properties, $add_underscore = true, $include_empty_fields = false)
	{
		$object = parent::getObject($objectType, $properties, $add_underscore, $include_empty_fields);
		
		if($object instanceof KalturaYouTubeDistributionProfile)
		{
			$upload = new Zend_File_Transfer_Adapter_Http();
			$files = $upload->getFileInfo();
         
			if(isset($files['sftp_public_key']))
			{
				$file = $files['sftp_public_key'];
				if ($file['size'])
				{
					$content = file_get_contents($file['tmp_name']);
					$object->sftpPublicKey = $content;
				}
			}
			
			if(isset($files['sftp_private_key']))
			{
				$file = $files['sftp_private_key'];
				if ($file['size'])
				{
					$content = file_get_contents($file['tmp_name']);
					$object->sftpPrivateKey = $content;
				}
			}
		}
		return $object;
	}
	
	protected function addProviderElements()
	{
		$element = new Zend_Form_Element_Hidden('providerElements');
		$element->setLabel('YouTube Specific Configuration');
		$element->setDecorators(array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'b'))));
		$this->addElements(array($element));
		
		// General
		$this->addElement('text', 'username', array(
			'label'			=> 'YouTube Account:',
			'filters'		=> array('StringTrim'),
		));
	
		$this->addElement('text', 'notification_email', array(
			'label'			=> 'Notification Email:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'owner_name', array(
			'label' => 'Owner Name:',
		));
		
		$this->addElement('select', 'target', array(
			'label' => 'Target:',
			'multioptions' => array(
				'upload,claim,fingerprint' => 'upload,claim,fingerprint', 
				'upload,claim' => 'upload,claim', 
				'claim,fingerprint' => 'claim,fingerprint',
			)
		));
		
		$this->addMetadataProfile();
		
		$this->addDisplayGroup(
			array('username', 'notification_email', 'owner_name', 'target', 'metadata_profile_id'), 
			'general', 
			array('legend' => 'General', 'decorators' => array('FormElements', 'Fieldset'))
		);
		
		// SFTP Configuration
		$this->addElement('text', 'sftp_host', array(
			'label'			=> 'SFTP Host:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'sftp_login', array(
			'label'			=> 'SFTP Login:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('file', 'sftp_public_key', array(
			'label' => 'SFTP Public Key:'
		));
		
		$this->addElement('file', 'sftp_private_key', array(
			'label' => 'SFTP Private Key:'
		));
		
		$this->addDisplayGroup(
			array('sftp_host', 'sftp_login', 'sftp_public_key', 'sftp_private_key'), 
			'sftp', 
			array('legend' => 'SFTP Configuration', 'decorators' => array('FormElements', 'Fieldset'))
		);
		
		//  Metadata
		$this->addElement('text', 'default_category', array(
			'label' => 'Default Category:',
		));
		
		$this->addDisplayGroup(
			array('default_category'), 
			'metadata',
			array('legend' => 'Metadata', 'decorators' => array('FormElements', 'Fieldset'))
		);
		
		// Community
		$this->addElement('select', 'allow_comments', array(
			'label' => 'Allow Comments:',
			'multioptions' => array(
				'' => 'Default', 
				'Always' => 'Always', 
				'Approve' => 'Approve',
				'Never' => 'Never',
			)
		));
		
		$this->addElement('select', 'allow_embedding', array(
			'label' => 'Allow Embedding:',
			'multioptions' => array(
				'' => 'Default', 
				'true' => 'True', 
				'false' => 'False',
			)
		));
		
		$this->addElement('select', 'allow_ratings', array(
			'label' => 'Allow Ratings:',
			'multioptions' => array(
				'' => 'Default', 
				'true' => 'True', 
				'false' => 'False',
			)
		));
		
		$this->addElement('select', 'allow_responses', array(
			'label' => 'Allow Responses:',
			'multioptions' => array(
				'' => 'Default', 
				'Always' => 'Always', 
				'Approve' => 'Approve',
				'Never' => 'Never',
			)
		));
		
		$this->addDisplayGroup(
			array('allow_comments', 'allow_embedding', 'allow_ratings', 'allow_responses'), 
			'community', 
			array('legend' => 'Community', 'decorators' => array('FormElements', 'Fieldset'))
		);
		
		$this->addElement('text', 'commercial_policy', array(
			'label' => 'Commercial Policy:'
		));
		
		$this->addElement('text', 'ugc_policy', array(
			'label' => 'UGC Policy:'
		));
		
		$this->addDisplayGroup(
			array('commercial_policy', 'ugc_policy'), 
			'policies', 
			array('legend' => 'Saved Policies', 'decorators' => array('FormElements', 'Fieldset'))
		);
	}
}