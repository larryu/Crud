<?php

namespace App\Containers\Crud\Tasks;

use Illuminate\Http\Request;
use App\Containers\Crud\Traits\DataGenerator;
use App\Containers\Crud\Traits\AngularFolderNamesResolver;

/**
 * CreateNgContainersTask Class.
 *
 * @author Johan Alvarez <llstarscreamll@hotmail.com>
 */
class CreateNgContainersTask
{
    use DataGenerator, AngularFolderNamesResolver;

    /**
     * Container name to generate.
     *
     * @var string
     */
    public $container;

    /**
     * Container entity to generate (database table name).
     *
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    private $indexStrToreplace = "\nexport const CONTAINERS = [";

    /**
     * The modules files to generate.
     *
     * @var array
     */
    public $files = [
        'form',
        'form-css',
        'form-html',
        'form-spec',
        'list-and-search',
        'list-and-search-css',
        'list-and-search-html',
        'list-and-search-spec',
        'abstract',
        'index',
    ];

    /**
     * The parsed fields from request.
     *
     * @var Illuminate\Support\Collection
     */
    public $parsedFields;

    /**
     * Create new CreateNgContainersTask instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->container = studly_case($request->get('is_part_of_package'));
        $this->tableName = $this->request->get('table_name');
        $this->parsedFields = $this->parseFields($this->request);
    }

    /**
     * @return bool
     */
    public function run()
    {
        // generate the main index file for containers
        $indexFilePath = $this->containersDir().'/../index.ts';
        $template = $this->templatesDir().'.Angular2/containers/main-index';
        $className = $this->entityName().'Containers';
        $fileName = './'.$this->slugEntityName().'';

        $this->setupIndexFile($indexFilePath, $template, $className, $fileName);

        foreach ($this->files as $file) {
            $plural = strpos($file, "list-and-search") !== false
                ? true
                : false;

            $atStart = strpos($file, "form") !== false || strpos($file, "abstract") !== false
                ? true
                : false;

            $containerFile = $this->containersDir()."/".$this->containerFile($file, $plural, $atStart);
            $template = $this->templatesDir().'.Angular2.containers.'.$file;

            $content = view($template, [
                'gen' => $this,
                'fields' => $this->parsedFields
            ]);

            file_put_contents($containerFile, $content) === false
                ? session()->push('error', "Error creating $file container file")
                : session()->push('success', "$file container creation success");
        }

        return true;
    }
}
