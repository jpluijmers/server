<?php

class DocumentPlugin extends KalturaPlugin implements IKalturaPlugin, IKalturaServices, IKalturaObjectLoader, IKalturaEventConsumers
{
	const PLUGIN_NAME = 'document';
	const DOCUMENT_OBJECT_CREATED_HANDLER = 'DocumentCreatedHandler';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		return null;
	}

	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{

		// ENTRY
		if($baseClass == 'entry' && $enumValue == entryType::DOCUMENT)
		{
			return new DocumentEntry();
		}
		
		
		// KALTURA FLAVOR PARAMS
		
		if($baseClass == 'KalturaFlavorParams')
		{
			switch($enumValue)
			{
				case DocumentAssetType::get()->coreValue(DocumentAssetType::PDF):
					return new KalturaPdfFlavorParams();
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::SWF):
					return new KalturaSwfFlavorParams();
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::DOCUMENT):
					return new KalturaDocumentFlavorParams();
				
				default:
					return null;	
			}
		}
	
		if($baseClass == 'KalturaFlavorParamsOutput')
		{
			switch($enumValue)
			{
				case DocumentAssetType::get()->coreValue(DocumentAssetType::PDF):
					return new KalturaPdfFlavorParamsOutput();
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::SWF):
					return new KalturaSwfFlavorParamsOutput();
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::DOCUMENT):
					return new KalturaDocumentFlavorParamsOutput();
				
				default:
					return null;	
			}
		}
		
		
		// OPERATION ENGINES
		
		if($baseClass == 'KOperationEngine' && $enumValue == KalturaConversionEngineType::PDF_CREATOR)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			
			return new KOperationEnginePdfCreator($constructorArgs['params']->pdfCreatorCmd, $constructorArgs['outFilePath']);
		}

		
		if($baseClass == 'KOperationEngine' && $enumValue == KalturaConversionEngineType::PDF2SWF)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			
			return new KOperationEnginePdf2Swf($constructorArgs['params']->pdf2SwfCmd, $constructorArgs['outFilePath']);
		}
		
		
		// KDL ENGINES
		
		if($baseClass == 'KDLOperatorBase' && $enumValue == conversionEngineType::PDF_CREATOR)
		{
			return new KDLTranscoderPdfCreator($enumValue);
		}
				
		if($baseClass == 'KDLOperatorBase' && $enumValue == conversionEngineType::PDF2SWF)
		{
			return new KDLTranscoderPdf2Swf($enumValue);
		}
		
		
		return null;
	}

	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// DOCUMENT ENTRY
		if($baseClass == 'entry' && $enumValue == entryType::DOCUMENT)
		{
			return 'DocumentEntry';
		}
		
		// FLAVOR PARAMS
		if($baseClass == 'flavorParams')
		{
			switch($enumValue)
			{
				case DocumentAssetType::get()->coreValue(DocumentAssetType::PDF):
					return 'PdfFlavorParams';
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::SWF):
					return 'SwfFlavorParams';
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::DOCUMENT):
					return 'DocumentFlavorParams';
				
				default:
					return null;	
			}
		}
	
		if($baseClass == 'flavorParamsOutput')
		{
			switch($enumValue)
			{
				case DocumentAssetType::get()->coreValue(DocumentAssetType::PDF):
					return 'PdfFlavorParamsOutput';
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::SWF):
					return 'SwfFlavorParamsOutput';
					
				case DocumentAssetType::get()->coreValue(DocumentAssetType::DOCUMENT):
					return 'DocumentFlavorParamsOutput';
				
				default:
					return null;	
			}
		}
		
		return null;
	}

	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'documents' => 'DocumentsService',
		);
		return $map;
	}
	
	/**
	 * @return string - the path to services.ct
	 */
	public static function getServiceConfig()
	{
		return realpath(dirname(__FILE__).'/config/document.ct');
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::DOCUMENT_OBJECT_CREATED_HANDLER,
		);
	}
}
