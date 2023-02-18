<?php namespace Wpjscc\Docs\Classes;

use File;
use Http;
use Lang;
use Config;
use Storage;
use DirectoryIterator;
use ApplicationException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Wpjscc\Docs\Classes\Contracts\Documentation;
use Wpjscc\Docs\Classes\Contracts\Page;
use Wpjscc\Docs\Classes\Contracts\PageList;
use Winter\Storm\Filesystem\Zip;

abstract class BaseDocumentation implements Documentation
{
    /**
     * The identifier of this documentation.
     */
    protected string $identifier;

    /**
     * The name of this documentation.
     */
    protected string $name;

    /**
     * The type of this documentation.
     */
    protected string $type;

    /**
     * The source disk which will be used for storage.
     */
    protected string $source = 'local';

    /**
     * The path where this documentation is loaded.
     */
    protected ?string $path = null;

    /**
     * The URL where the compiled documentation can be found.
     */
    protected ?string $url = null;

    /**
     * The URL to the source repository for this documentation.
     */
    protected ?string $repositoryUrl = null;
    /**
     * The URL to the source repository for this documentation.
     */
    protected ?string $tocUrl = null;

    /**
     * The URL template for editing a page in the source repository.
     */
    protected ?string $repositoryEditUrl = null;

    /**
     * The subfolder within the ZIP file in which this documentation is stored.
     */
    protected ?string $zipFolder = null;

    /**
     * Is this documentation available?
     */
    protected ?bool $available = null;

    /**
     * Is this documentation downloaded?
     */
    protected ?bool $downloaded = null;

    /**
     * The storage disk where processed and downloaded documentation is stored.
     */
    protected ?Filesystem $storageDisk = null;

    /**
     * Paths to ignore when collating available pages and assets.
     */
    protected array $ignoredPaths = [];

    public $local;
    public $is_translate;
    public $ignoreTranslates;
    public $forceTranslates;

    /**
     * Constructor.
     *
     * Expects an identifier for the documentation in the format of Author.Plugin.Doc (eg. Winter.Docs.Docs). The
     * second parameter is the configuration for this documentation.
     *
     * @param string $identifier
     * @param array $config
     */
    public function __construct(string $identifier, array $config = [])
    {
        $this->identifier = $identifier;
        $this->name = $config['name'];
        $this->type = $config['type'];
        $this->source = $config['source'];
        $this->path = $config['path'] ?? null;
        $this->url = $config['url'] ?? null;
        $this->local = $config['local'] ?? 'zh-CN';
        $this->is_translate = $config['is_translate'] ?? false;
        $this->zipFolder = $config['zipFolder'] ?? '';
        $this->ignoredPaths = $config['ignorePaths'] ?? [];
        $this->ignoreTranslates = $config['ignoreTranslates'] ?? [];
        $this->forceTranslates = $config['forceTranslates'] ?? [];
        $this->repositoryUrl = $config['repository']['url'] ?? null;
        $this->tocUrl = $config['repository']['tocUrl'] ?? null;
        $this->repositoryEditUrl = $config['repository']['editUrl'] ?? null;
    }

    /**
     * Gets the identifier of the documentation.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Gets the name of this documentation.
     */
    public function getName(): string
    {
        return Lang::get($this->name);
    }

    /**
     * Gets the type of this documentation.
     *
     * The type will be one of the following:
     *  - `user`: Documentation intended for end-users or site administrators.
     *  - `developer`: Documentation intended for developers.
     *  - `api`: API documentation generated from PHP source code.
     *  - `events`: Event documentation generated from PHP source code.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Determines if this documentation is remotely sourced.
     */
    public function isRemote(): bool
    {
        return ($this->source === 'remote');
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(): bool
    {
        if (!is_null($this->available)) {
            return $this->available;
        }

        return $this->available = (
            $this->getStorageDisk()->exists($this->getProcessedPath('page-map.json'))
            && $this->getStorageDisk()->exists($this->getProcessedPath('toc.json'))
        );
    }

    /**
     * @inheritDoc
     */
    public function isProcessed(): bool
    {
        return $this->isDownloaded() && $this->isAvailable();
    }

    /**
     * @inheritDoc
     */
    public function isDownloaded(): bool
    {
        if (!is_null($this->downloaded)) {
            return $this->downloaded;
        }

        if (!$this->isRemote()) {
            return $this->downloaded = true;
        }

        // If a remotely-sourced documentation is available, we'll assume it's downloaded.
        if ($this->isAvailable()) {
            return $this->downloaded = true;
        }

        return $this->downloaded = File::exists($this->getDownloadPath('archive.zip'));
    }

    /**
     * Downloads a remote ZIP file for the documentation.
     *
     * The downloaded file will be placed at the expected location and extracted for processing
     */
    public function download(): void
    {
        // Local sources do not need to be downloaded
        if (!$this->isRemote()) {
            return;
        }

        if (!$this->isRemoteAvailable()) {
            throw new ApplicationException(
                sprintf(
                    'Could not retrieve the documentation for "%s" from the remote source "%s"',
                    $this->identifier,
                    $this->source
                )
            );
        }

        // Create temporary location
        if (!File::exists($this->getDownloadPath())) {
            File::makeDirectory($this->getDownloadPath(), 0777, true);
        }

        // Download ZIP file
        $http = Http::get($this->url, function ($http) {
            $http->toFile($this->getDownloadPath('archive.zip'));
        });

        if (!$http->ok) {
            throw new ApplicationException(
                sprintf(
                    'Could not retrieve the documentation for "%s" from the remote source "%s"',
                    $this->identifier,
                    $this->source
                )
            );
        }
    }

    /**
     * Extracts the downloaded ZIP file
     *
     * The ZIP file will be extracted to an "extracted" subfolder within the download path, and the source
     * made available for processing.
     *
     * @throws ApplicationException If the docs ZIP has not been downloaded
     */
    public function extract(): void
    {
        // Local sources do not need to be downloaded, therefore don't need to be extracted
        if (!$this->isRemote()) {
            return;
        }

        if (!$this->isDownloaded()) {
            throw new ApplicationException(
                sprintf(
                    'You must download the "%s" documentation first',
                    $this->identifier
                )
            );
        }

        // Create extracted location
        if (!File::exists($this->getDownloadPath('extracted'))) {
            File::makeDirectory($this->getDownloadPath('extracted'), 0777, true);
        } else {
            File::deleteDirectory($this->getDownloadPath('extracted'));
        }

        // Create collated location
        if (!File::exists($this->getDownloadPath('collated'))) {
            File::makeDirectory($this->getDownloadPath('collated'), 0777, true);
        } else {
            File::deleteDirectory($this->getDownloadPath('collated'));
        }

        // Extract ZIP to location
        $zip = new Zip();
        $zip->open($this->getDownloadPath('archive.zip'));
        $toExtract = null;

        if (!empty($this->zipFolder)) {
            $toExtract = [];

            // Only get necessary files from the ZIP file
            for ($i = 1; $i < $zip->numFiles; ++$i) {
                $filename = $zip->statIndex($i)['name'];

                if (strpos($filename, $this->zipFolder) === 0) {
                    $toExtract[] = $filename;
                }
            }
        }

        $zip->extractTo($this->getDownloadPath('extracted'), $toExtract);

        // Move remaining files into location
        $dir = new DirectoryIterator($this->getDownloadPath('extracted/' . $this->zipFolder));

        foreach ($dir as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($this->zipFolder) {
                $relativePath = str_replace($this->getDownloadPath('extracted/' . $this->zipFolder . '/'), '', $item->getPathname());

                rename($item->getPathname(), $this->getDownloadPath('collated/'. $this->zipFolder.'/'. $relativePath));
            } else {
                // $relativePath = str_replace($this->getDownloadPath('extracted/'), '', $item->getPathname());
                rename($item->getPathname(), $this->getDownloadPath('collated/'));
            }
           
        }

        // Remove ZIP folder
        File::deleteDirectory($this->getDownloadPath('extracted/' . $this->zipFolder));

        // Remove ZIP file
        File::delete($this->getDownloadPath('archive.zip'));
    }

    /**
     * Deletes any temporary downloads and extracted files.
     *
     * @return void
     */
    public function cleanupDownload()
    {
        if (File::exists($this->getDownloadPath('archive.zip'))) {
            File::delete($this->getDownloadPath('archive.zip'));
        }

        if (File::exists($this->getDownloadPath('extracted'))) {
            File::deleteDirectory($this->getDownloadPath('extracted'));
        }

        if (File::exists($this->getDownloadPath('collated'))) {
            File::deleteDirectory($this->getDownloadPath('collated'));
        }
    }

    /**
     * @inheritDoc
     */
    abstract public function process($filename = ''): void;

    /**
     * @inheritDoc
     */
    abstract public function getPageList(): PageList;

    /**
     * @inheritDoc
     */
    public function getRepositoryUrl(): ?string
    {
        return $this->repositoryUrl;
    }
    /**
     * @inheritDoc
     */
    public function getTocUrl(): ?string
    {
        return $this->tocUrl;
    }

    /**
     * Returns a URL to edit the source of the given page.
     *
     * This requires the repository edit URL to be provided in the documentation config, otherwise,
     * `null` will be returned.
     *
     * @param Page $page
     * @return string|null
     */
    public function getRepositoryEditUrl(Page $page): ?string
    {
        if (is_null($this->repositoryEditUrl)) {
            return null;
        }

        return sprintf($this->repositoryEditUrl, $page->getPath());
    }

    /**
     * Resets cached states (is available?, is downloaded?)
     *
     * @return void
     */
    public function resetState(): void
    {
        $this->downloaded = null;
        $this->available = null;
    }

    /**
     * Checks if a remotely-sourced documentation ZIP file is available.
     */
    protected function isRemoteAvailable(): bool
    {
        return Http::head($this->url)->ok;
    }

    /**
     * Provides the path where unprocessed documentation will be stored.
     *
     * This will always be stored in temp storage, and assumes that the documentation is processed
     * and extracted if it is a remotely sourced documentation.
     *
     * @param string $suffix
     * @return string
     */
    public function getProcessPath(string $suffix = ''): string
    {
        if ($this->isRemote()) {
            $path = $this->getDownloadPath('collated');
            if (!empty($suffix)) {
                $path .= '/' . (ltrim(str_replace(['\\', '/'], '/', $suffix), '/'));
            }
            return $path;
        }

        $path = rtrim($this->path, '/');
        if (!empty($suffix)) {
            $path .= '/' . (ltrim(str_replace(['\\', '/'], '/', $suffix), '/'));
        }
        return $path;
    }

    /**
     * Get all unprocessed files that meet the given extension(s).
     */
    public function getProcessFiles(string|array $extension = []): array
    {
        $extensions = (is_string($extension))
            ? [$extension]
            : $extension;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->getProcessPath())
        );
        $found = [];

        foreach ($files as $file) {
            if (File::isFile($file) && (!count($extensions) || in_array(File::extension($file), $extensions))) {
                $relative = ltrim(str_replace($this->getProcessPath(), '', $file), '/');

                foreach ($this->ignoredPaths as $path) {
                    if ($relative === $path || str_starts_with($relative, $path)) {
                        continue 2;
                    }
                }

                $found[] = $relative;
            }
        }

        return $found;
    }

    /**
     * Provides the path where processed documentation will be stored.
     *
     * This path will be used on the storage disk.
     */
    public function getProcessedPath(string $suffix = ''): string
    {
        $path = Config::get('wpjscc.docs::storage.processedPath', 'docs/processed') . '/' . $this->getPathIdentifier();

        // Normalise suffix path
        if (!empty($suffix)) {
            $path .= '/' . (ltrim(str_replace(['\\', '/'], '/', $suffix), '/'));
        }

        return $path;
    }

    /**
     * Gets the contents of a file in the processed storage.
     *
     * Returns `null` if the file cannot be found or read.
     */
    public function getProcessedFile(string $path): ?string
    {
        return $this->getStorageDisk()->get(
            $this->getProcessedPath($path)
        ) ?? null;
    }

    /**
     * Provides the path where downloaded remotely-sourced documentation will be stored.
     *
     * This will always be stored in temp storage, as it is expected that a download will be
     * immediately processed afterwards.
     */
    public function getDownloadPath(string $suffix = ''): string
    {
        $path = temp_path(Config::get('wpjscc.docs::storage.downloadPath', 'docs/download') . '/' . $this->getPathIdentifier());

        // Normalise suffix path
        if (!empty($suffix)) {
            $path .= '/' . (ltrim(str_replace(['\\', '/'], '/', $suffix), '/'));
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Provides a path identifier for this particular documentation.
     *
     * This is a kebab-case string that will be used as a subfolder for the processed and download paths.
     */
    protected function getPathIdentifier(): string
    {
        return kebab_case(strtolower(str_replace('.', '-', $this->identifier)));
    }

    /**
     * Gets the storage disk.
     */
    protected function getStorageDisk(): Filesystem
    {
        if ($this->storageDisk) {
            return $this->storageDisk;
        }

        return $this->storageDisk = Storage::disk(
            Config::get('wpjscc.docs::storage.disk', 'local')
        );
    }

    /**
     * Determines if the storage disk is using the "local" driver.
     */
    protected function isLocalStorage(): bool
    {
        return File::isLocalDisk($this->getStorageDisk());
    }
}
