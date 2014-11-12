<?php

class AnyclipFeedIngester extends VideoFeedIngester {
	protected static $API_WRAPPER = 'AnyclipApiWrapper';
	protected static $PROVIDER = 'anyclip';
	protected static $FEED_URL = 'https://mrss.anyclip.com/$1.xml';

	public function downloadFeed( $startDate ) {
		wfProfileIn( __METHOD__ );

		$url = $this->initFeedUrl( $startDate );

		print( "Connecting to $url...\n" );

		$content = $this->getUrlContent( $url );
		if ( !$content ) {
			$this->logger->videoErrors( "ERROR: problem downloading content.\n" );
			wfProfileOut( __METHOD__ );

			return 0;
		}

		wfProfileOut( __METHOD__ );

		return $content;
	}

	private function initFeedUrl( $getAllVideos ) {
		if ( $getAllVideos ) {
			$url = str_replace( '$1', 'full', static::$FEED_URL );
		} else {
			$url = str_replace( '$1', 'daily', static::$FEED_URL );
		}

		return $url;
	}

	public function import( $content = '', array $params = [] ) {
		wfProfileIn( __METHOD__ );

		$articlesCreated = 0;

		$doc = new DOMDocument( '1.0', 'UTF-8' );
		@$doc->loadXML( $content );
		$items = $doc->getElementsByTagName( 'item' );
		$numItems = $items->length;
		$this->logger->videoFound( $numItems );

		for ( $i = 0; $i < $numItems; $i++ ) {
			$item = $items->item( $i );

			// check for video name
			$elements = $item->getElementsByTagName( 'title' );
			if ( $elements->length > 0 ) {
				$clipData['titleName'] = html_entity_decode( $elements->item(0)->textContent );
				$clipData['uniqueName'] = $clipData['titleName'];
			} else {
				$this->logger->videoSkipped();
				continue;
			}

			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'description' );
			$clipData['description'] = ( $elements->length > 0 ) ? $elements->item(0)->textContent : '' ;

			// check for video id
			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'embed' );
			if ( $elements->length > 0 ) {
				foreach ( $elements->item(0)->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'param' ) as $element ) {
					if ( $element->getAttribute( 'name' ) == 'clipId' ) {
						$clipData['videoId'] = $element->textContent;
					}
				}
			}

			if ( !array_key_exists( 'videoId', $clipData ) ) {
				$this->logger->videoWarnings( "ERROR: videoId NOT found for {$clipData['titleName']} - {$clipData['description']}.\n" );
				continue;
			}

			if ( empty( $clipData['videoId'] ) ) {
				$this->logger->videoWarnings( "ERROR: Empty videoId for {$clipData['titleName']} - {$clipData['description']}.\n" );
				continue;
			}

			// check for nonadult videos
			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'rating' );
			$clipData['ageGate'] = ( $elements->length > 0 && $elements->item(0)->textContent == 'nonadult' ) ? 0 : 1;

			if ( $clipData['ageGate'] ) {
				$this->logger->videoSkipped( "SKIP: Skipping adult video: {$clipData['titleName']} ({$clipData['videoId']}).\n" );
				continue;
			}

			$clipData['ageRequired'] = 0;

			$this->getTitleName( $clipData['titleName'], $clipData['videoId'] );

			$clipData['published'] = strtotime( $item->getElementsByTagName( 'pubDate' )->item(0)->textContent );
			$clipData['videoUrl'] = $item->getElementsByTagName( 'link' )->item(0)->textContent;

			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'thumbnail' );
			$clipData['thumbnail'] = ( $elements->length > 0 ) ? $elements->item(0)->getAttribute( 'url' ) : '' ;

			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'keywords' );
			$clipData['keywords'] = ( $elements->length > 0 ) ? $elements->item(0)->textContent : '' ;

			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'category' );
			if ( $elements->length > 0 ) {
				$clipData['category'] = $this->getCategory( $elements->item(0)->textContent );
				if ( !empty( $clipData['category'] ) && $clipData['category'] == 'Movies' ) {
					$clipData['type'] = 'Clip';
				}

				$clipData['name'] = $elements->item(0)->getAttribute( 'label' );
			}

			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'content' );
			if ( $elements->length > 0 ) {
				$clipData['language'] = $this->getCLDRCode( $elements->item(0)->getAttribute( 'lang' ), 'language', false );
				$clipData['duration'] = $elements->item(0)->getAttribute( 'duration' );
			}

			$genres = array();
			$elements = $item->getElementsByTagName( 'genre' );
			foreach ( $elements as $element ) {
				$genres[] = $element->textContent;
			}
			$clipData['genres'] = implode( ', ', $genres );

			$actors = array();
			$elements = $item->getElementsByTagNameNS( 'http://search.yahoo.com/mrss/', 'credit' );
			foreach ( $elements as $element ) {
				if ( $element->getAttribute( 'role' ) == 'actor' ) {
					$actors[] = $element->textContent;
				}
			}
			$clipData['actors'] = implode( ', ', $actors );

			$clipData['hd'] = 0;
			$clipData['provider'] = 'anyclip';

			$articlesCreated += $this->createVideo( $clipData );
		}

		wfProfileOut( __METHOD__ );

		return $articlesCreated;
	}

	/**
	 * Create a list of category names to add to the new file page
	 * @param array $videoData
	 * @param array $addlCategories
	 * @return array $categories
	 */
	public function generateCategories(array $videoData, array $addlCategories) {
		wfProfileIn( __METHOD__ );

		$addlCategories[] = 'AnyClip';
		$addlCategories[] = 'Entertainment';
		if ( stristr( $this->metaData['titleName'], 'trailer' ) ) {
			$addlCategories[] = 'Trailers';
		}

		if ( !empty( $this->metaData['name'] ) ) {
			$addlCategories[] = $this->metaData['name'];
			$addition = $this->getAdditionalPageCategory( $this->metaData['name'] );
			if ( !empty( $addition ) ) {
				$addlCategories[] = $addition;
			}
		}

		wfProfileOut( __METHOD__ );

		return wfGetUniqueArrayCI( $addlCategories );
	}

	/**
	 * generate metadata
	 * @param array $videoData
	 * @return array
	 */
	public function generateMetadata( array $videoData ) {
		$metadata = parent::generateMetadata( $videoData );
		$metadata['videoUrl'] = empty( $videoData['videoUrl'] ) ? '' : $videoData['videoUrl'];
		$metadata['uniqueName'] = empty( $videoData['uniqueName'] ) ? '' : $videoData['uniqueName'];

		return $metadata;
	}

	/**
	 * get title
	 * @param string $titleName
	 * @param string $code - video id
	 */
	protected function getTitleName( &$titleName, $code ) {
		wfProfileIn( __METHOD__ );

		$url = AnyclipApiWrapper::getApi( $code );
		$response = Http::request( 'GET', $url, array( 'noProxy' => true ) );
		if ( $response !== false ) {
			$content = json_decode( $response, true );

			$title = AnyclipApiWrapper::getClipName( $content );
			if ( !empty( $title ) ) {
				$titleName = $title;
			}
		}

		wfProfileOut( __METHOD__ );
	}

}