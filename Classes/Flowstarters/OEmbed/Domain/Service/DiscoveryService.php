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

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class DiscoveryService {

	const SERVICE_LINK_TAG_PATTERN = '#<link(?:[^>]*)type="(?P<type>application/json|text/xml)\+oembed"(?:[^>]*)>#i';
	const SERVICE_URI_PATTERN = '/href="(?P<uri>[^"]+)"/i';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var \Flowstarters\OEmbed\Domain\Service\MappingService
	 */
	protected $mappingService;

	/**
	 * Set up the service
	 */
	public function initializeObject() {
		$engine = new \TYPO3\Flow\Http\Client\CurlEngine();
		$this->browser->setRequestEngine($engine);
	}

	/**
	 * @param string|\TYPO3\Flow\Http\Uri $uri
	 * @return \Flowstarters\OEmbed\Domain\Model\ResourceInterface
	 * @throws \Flowstarters\OEmbed\Exception If neither JSON nor XML service designators were found
	 * @throws \InvalidArgumentException If $uri is not valid
	 */
	public function getResource($uri) {
		if (is_string($uri)) {
			$uri = new \TYPO3\Flow\Http\Uri($uri);
		}
		if (!$uri instanceof \TYPO3\Flow\Http\Uri) {
			throw new \InvalidArgumentException('$uri must be an URI object or a valid string representation of an URI.', 1333443624);
		}

		try {
			$serviceDesignators = $this->discoverOEmbedServiceDesignators($uri);
			if (isset($serviceDesignators['application/json'])) {
				$oEmbedData = $this->browser->request($serviceDesignators['application/json'])->getContent();
				$oEmbedObject = $this->mappingService->mapJsonToObject($oEmbedData);
			} elseif (isset($serviceDesignators['text/xml'])) {
				$oEmbedData = $this->browser->request($serviceDesignators['text/xml'])->getContent();
				$oEmbedObject = $this->mappingService->mapXmlToObject($oEmbedData);
			} else {
				throw new \Flowstarters\OEmbed\Exception('Neither application/json nor text/xml oEmbed is supported by your resource URI, it seems to only supply: ' . htmlspecialchars(implode(', ', array_keys($serviceDesignators))), 1359714227);
			}
			return $oEmbedObject;
		} catch (\TYPO3\Flow\Http\Client\CurlEngineException $exception) {
			return NULL;
		}
	}

	/**
	 * @param \TYPO3\Flow\Http\Uri $uri
	 * @return array
	 * @throws \Flowstarters\OEmbed\Exception
	 */
	protected function discoverOEmbedServiceDesignators(\TYPO3\Flow\Http\Uri $uri) {
		//TODO: Cache oEmbedDesignators, if possible even only the base service uri
		$possibleOEmbedDesignators = array();

		$rawContent = $this->browser->request($uri)->getContent();
		$matchedServiceTags = array();
		preg_match_all(self::SERVICE_LINK_TAG_PATTERN, $rawContent, $matchedServiceTags);

		if (isset($matchedServiceTags[0])) {
			foreach ($matchedServiceTags[0] as $index => $link) {
				$matches = array();
				if (preg_match(self::SERVICE_URI_PATTERN, $link, $matches)) {
					$possibleOEmbedDesignators[$matchedServiceTags['type'][$index]] = $matches['uri'];
				}
			}
		} else {
			throw new \Flowstarters\OEmbed\Exception('There was no oEmbed service URI discovered for the given given Resource URI: ' . htmlspecialchars($uri), 1359714226);
		}

		return $possibleOEmbedDesignators;
	}
}

?>