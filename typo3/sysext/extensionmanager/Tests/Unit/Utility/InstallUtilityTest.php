<?php
namespace TYPO3\CMS\Extensionmanager\Tests\Unit\Utility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case
 */
class InstallUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @var array
	 */
	protected $extensionData = array();

	/**
	 * @var array List of created fake extensions to be deleted in tearDown() again
	 */
	protected $fakedExtensions = array();

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Extensionmanager\Utility\InstallUtility|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $installMock;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->extensionKey = 'dummy';
		$this->extensionData = array(
			'key' => $this->extensionKey
		);
		$this->installMock = $this->getAccessibleMock(
			'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
			array(
				'isLoaded',
				'loadExtension',
				'unloadExtension',
				'processDatabaseUpdates',
				'processRuntimeDatabaseUpdates',
				'reloadCaches',
				'processCachingFrameworkUpdates',
				'saveDefaultConfiguration',
				'enrichExtensionWithDetails',
				'ensureConfiguredDirectoriesExist',
				'importInitialFiles'
			),
			array(),
			'',
			FALSE
		);
		$dependencyUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');
		$this->installMock->_set('dependencyUtility', $dependencyUtility);
		$this->installMock->expects($this->any())
			->method('enrichExtensionWithDetails')
			->with($this->extensionKey)
			->will($this->returnCallback(array($this, 'getExtensionData')));
	}

	/**
	 * @return array
	 */
	public function getExtensionData() {
		return $this->extensionData;
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		foreach ($this->fakedExtensions as $extension => $dummy) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir(PATH_site . 'typo3temp/' . $extension, TRUE);
		}
		parent::tearDown();
	}

	/**
	 * Creates a fake extension inside typo3temp/. No configuration is created,
	 * just the folder
	 *
	 * @return string The extension key
	 */
	protected function createFakeExtension() {
		$extKey = strtolower(uniqid('testing'));
		$absExtPath = PATH_site . 'typo3temp/' . $extKey;
		$relPath = 'typo3temp/' . $extKey . '/';
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($absExtPath);
		$this->fakedExtensions[$extKey] = array(
			'siteRelPath' => $relPath
		);
		return $extKey;
	}

	/**
	 * @test
	 */
	public function installCallsProcessRuntimeDatabaseUpdates() {
		$this->installMock->expects($this->once())
			->method('processRuntimeDatabaseUpdates')
			->with($this->extensionKey);

		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCachesInGroup');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->install($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function installCallsLoadExtension() {
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCachesInGroup');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->expects($this->once())->method('loadExtension');
		$this->installMock->install($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function installCallsFlushCachesIfClearCacheOnLoadIsSet() {
		$this->extensionData['clearcacheonload'] = TRUE;
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCaches');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->install($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function installCallsFlushCachesIfClearCacheOnLoadCamelCasedIsSet() {
		$this->extensionData['clearCacheOnLoad'] = TRUE;
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCaches');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->install($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function installationOfAnExtensionWillCallEnsureThatDirectoriesExist() {
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCachesInGroup');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->expects($this->once())->method('ensureConfiguredDirectoriesExist');
		$this->installMock->install($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function installCallsReloadCaches() {
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCachesInGroup');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->expects($this->once())->method('reloadCaches');
		$this->installMock->install('dummy');
	}

	/**
	 * @test
	 */
	public function installCallsSaveDefaultConfigurationWithExtensionKey() {
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManagerMock->expects($this->once())->method('flushCachesInGroup');
		$this->installMock->_set('cacheManager', $cacheManagerMock);
		$this->installMock->expects($this->once())->method('saveDefaultConfiguration')->with('dummy');
		$this->installMock->install('dummy');
	}

	/**
	 * @test
	 */
	public function uninstallCallsUnloadExtension() {
		$this->installMock->expects($this->once())->method('unloadExtension');
		$this->installMock->uninstall($this->extensionKey);
	}

	/**
	 * @test
	 */
	public function processDatabaseUpdatesCallsUpdateDbWithExtTablesSql() {
		$extKey = $this->createFakeExtension();
		$extPath = PATH_site . 'typo3temp/' . $extKey . '/';
		$extTablesFile = $extPath . 'ext_tables.sql';
		$fileContent = 'DUMMY TEXT TO COMPARE';
		file_put_contents($extTablesFile, $fileContent);
		$installMock = $this->getAccessibleMock(
			'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
			array('updateDbWithExtTablesSql', 'importStaticSqlFile', 'importT3DFile'),
			array(),
			'',
			FALSE
		);
		$dependencyUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');
		$installMock->_set('dependencyUtility', $dependencyUtility);

		$installMock->expects($this->once())->method('updateDbWithExtTablesSql')->with($this->stringStartsWith($fileContent));
		$installMock->processDatabaseUpdates($this->fakedExtensions[$extKey]);
	}

	/**
	 * @test
	 */
	public function processDatabaseUpdatesCallsImportStaticSqlFile() {
		$extKey = $this->createFakeExtension();
		$extRelPath = 'typo3temp/' . $extKey . '/';
		$installMock = $this->getAccessibleMock(
			'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
			array('importStaticSqlFile', 'updateDbWithExtTablesSql', 'importT3DFile'),
			array(),
			'',
			FALSE
		);
		$dependencyUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');
		$installMock->_set('dependencyUtility', $dependencyUtility);
		$installMock->expects($this->once())->method('importStaticSqlFile')->with($extRelPath);
		$installMock->processDatabaseUpdates($this->fakedExtensions[$extKey]);
	}

	/**
	 * @return array
	 */
	public function processDatabaseUpdatesCallsImportFileDataProvider() {
		return array(
			'T3D file' => array(
				'data.t3d'
			),
			'XML file' => array(
				'data.xml'
			)
		);
	}

	/**
	 * @param string $fileName
	 * @test
	 * @dataProvider processDatabaseUpdatesCallsImportFileDataProvider
	 */
	public function processDatabaseUpdatesCallsImportFile($fileName) {
		$extKey = $this->createFakeExtension();
		$absPath = PATH_site . $this->fakedExtensions[$extKey]['siteRelPath'];
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($absPath . '/Initialisation');
		file_put_contents($absPath . '/Initialisation/' . $fileName, 'DUMMY');
		$installMock = $this->getAccessibleMock(
			'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
			array('updateDbWithExtTablesSql', 'importStaticSqlFile', 'importT3DFile'),
			array(),
			'',
			FALSE
		);
		$dependencyUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');
		$installMock->_set('dependencyUtility', $dependencyUtility);
		$installMock->expects($this->once())->method('importT3DFile')->with($this->fakedExtensions[$extKey]['siteRelPath']);
		$installMock->processDatabaseUpdates($this->fakedExtensions[$extKey]);
	}

	/**
	 * @return array
	 */
	public function importT3DFileDoesNotImportFileIfAlreadyImportedDataProvider() {
		return array(
			'Import T3D file when T3D was imported before extension to XML' => array(
				'data.t3d',
				'dataImported',
				'data.t3d',
			),
			'Import T3D file when a file was imported after extension to XML' => array(
				'data.t3d',
				'data.t3d',
				'dataImported'
			),
			'Import XML file when T3D was imported before extension to XML' => array(
				'data.xml',
				'dataImported',
				'data.t3d'
			),
			'Import XML file when a file was imported after extension to XML' => array(
				'data.xml',
				'data.t3d',
				'dataImported'
			)
		);
	}

	/**
	 *
	 * @param string $fileName
	 * @param string $registryNameReturnsFalse
	 * @param string $registryNameReturnsTrue
	 * @test
	 * @dataProvider importT3DFileDoesNotImportFileIfAlreadyImportedDataProvider
	 */
	public function importT3DFileDoesNotImportFileIfAlreadyImported($fileName, $registryNameReturnsFalse, $registryNameReturnsTrue) {
		$extKey = $this->createFakeExtension();
		$absPath = PATH_site . $this->fakedExtensions[$extKey]['siteRelPath'];
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($absPath . 'Initialisation');
		file_put_contents($absPath . 'Initialisation/' . $fileName, 'DUMMY');
		$registryMock = $this->getMock('\\TYPO3\\CMS\\Core\\Registry', array('get', 'set'));
		$registryMock
			->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('extensionDataImport', $this->fakedExtensions[$extKey]['siteRelPath'] . 'Initialisation/' . $registryNameReturnsFalse, NULL, FALSE),
					array('extensionDataImport', $this->fakedExtensions[$extKey]['siteRelPath'] . 'Initialisation/' . $registryNameReturnsTrue, NULL, TRUE),
				)
			));
		$installMock = $this->getAccessibleMock(
			'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
			array('getRegistry', 'getImportExportUtility'),
			array(),
			'',
			FALSE
		);
		$dependencyUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');
		$installMock->_set('dependencyUtility', $dependencyUtility);
		$installMock->_set('registry', $registryMock);
		$installMock->expects($this->never())->method('getImportExportUtility');
		$installMock->_call('importT3DFile', $this->fakedExtensions[$extKey]['siteRelPath']);
	}
}
