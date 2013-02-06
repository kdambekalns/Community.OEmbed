<?php
namespace Flowstarters\OEmbed\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowstarters.OEmbed".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class MappingService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param string $data
	 * @return \Flowstarters\OEmbed\Domain\Model\ResourceInterface
	 */
	public function mapJsonToObject($data) {
		$dataArray = json_decode($data, TRUE);
		return $this->mapArrayToObject($dataArray);
	}

	/**
	 * @param string $data
	 * @return \Flowstarters\OEmbed\Domain\Model\ResourceInterface
	 */
	public function mapXmlToObject($data) {
		$xml = new \SimpleXMLElement($data);
		return $this->mapArrayToObject((array) $xml);
	}

	/**
	 * @param array $dataArray
	 * @return \Flowstarters\OEmbed\Domain\Model\ResourceInterface
	 * @throws \Flowstarters\OEmbed\Exception
	 */
	protected function mapArrayToObject(array $dataArray) {
		if (isset($dataArray['type']) && $this->objectTypeExists($dataArray['type'])) {
			$className = $this->deriveClassName($dataArray['type']);
			$oEmbedResource = new $className;
			return $this->mapDataToObject($dataArray, $oEmbedResource);
		} else {
			throw new \Flowstarters\OEmbed\Exception('Retrieved data contained no type, could not map to oEmbed object', 1359741982);
		}
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function deriveClassName($type) {
		return 'Flowstarters\\OEmbed\\Domain\Model\\' . ucfirst($type) . 'Resource';
	}

	/**
	 * @param array $dataArray
	 * @param \Flowstarters\OEmbed\Domain\Model\ResourceInterface $oEmbedResource
	 * @return \Flowstarters\OEmbed\Domain\Model\ResourceInterface
	 */
	protected function mapDataToObject($dataArray, $oEmbedResource) {
		$additionalProperties = array();
		foreach ($dataArray as $dataKey => $propertyValue) {
			$propertyName = $this->derivePropertyNameFromDataKey($dataKey);
			$possibleSetterName = 'set' . ucfirst($propertyName);
			if (is_callable(array($oEmbedResource, $possibleSetterName))) {
				call_user_func(array($oEmbedResource, $possibleSetterName), $propertyValue);
			} else {
				$additionalProperties[$propertyName] = $propertyValue;
			}
		}
		$oEmbedResource->setAdditionalProperties($additionalProperties);

		return $oEmbedResource;
	}

	/**
	 * @param string $dataKey
	 * @return string
	 */
	protected function derivePropertyNameFromDataKey($dataKey) {
		return lcfirst(implode('', array_map('ucfirst', explode('_', $dataKey))));
	}

	/**
	 * @param string $type
	 * @return boolean
	 */
	protected function objectTypeExists($type) {
		return $this->objectManager->isRegistered($this->deriveClassName($type));
	}
}

?>