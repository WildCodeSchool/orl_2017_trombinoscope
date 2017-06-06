# UPLOAD


- définir le chemin vers le dossier d'upload utilisé (peut varier donc on le met dans les paramètres)
    dans parameters.yml ajouter la ligne
        upload_directory: '%kernel.root_dir%/../web/uploads'
        
- définir le champ en bdd 
    le champ sera relié à un champ File en formulaire 
    mais en BDD uniquement un string de 255
    
    pour gérer ce "double" statut, il faut ajouter l'annotation Assert\File() dans l'entité (cela signifie que ce champs peut être un objet de type Symfony\Component\HttpFoundation\File\File (équivaut à $_FILES);
    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="text")
     * @Assert\File()
     */
    private $picture;
    
    Remarque, @Assert\File peut prendre des paramètre (par ex, type mime du fichier)
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Please upload a valid PDF"
     * )
     
     Si on ne travaille qu'avec des images, il est possible d'utiliser directement
     * @Assert\Image() qui restreind uniquement le File aux types MIME d'images et ajoute des validateur de taille et ratio en plus ...
     /**
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 400,
     *     minHeight = 200,
     *     maxHeight = 400
     * )
     */
     
# Dans le formulaire, ajouter le champ File
    ->add('picture', FileType::class)
    
# Dans le controlleur, ajouter le code pour gérer l'upload
cas d'un ajout :


    // $file stores the uploaded PDF file
     /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
     $file = $person->getPicture();
    
     // Generate a unique name for the file before saving it
     $fileName = md5(uniqid()).'.'.$file->guessExtension();
    
     // Move the file to the directory where brochures are stored
     $file->move(
         $this->getParameter('upload_directory'),
         $fileName
     );
    
     $person->setPicture($fileName);
     
Pour afficher dans le twig

    <img src="{{ asset('uploads/' ~ person.picture) }}" alt="{{ person.lastname }}"/>


Cas d'un upload, le formulaire attend un objet File pour le champ File du form
il faut donc ajouter du code avant le createForm

        $person->setPicture(
            new File($this->getParameter('upload_directory').'/' .
                $person->getPicture())
        );

Pour l'affichage du formulaire d'upload (form non encore soumis), c'est un objet File qui est passé à twig et non un string
il faut donc rajouter un 

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $file = $person->getPicture();
            ...
        }   else {
            $person->setPicture(basename($person->getPicture()));
        }

# Service
Le code de l'upload est dans le controlleur et on est obligé de la répeter pour chaque formulaire.
Pour éviter cela, on peut déporter ce code propre à l'upload dans un service.

    // app/config/service.yml
    trombi.file_uploader:
        class: TrombiBundle\Service\FileUploader
        arguments: ['%upload_directory%']
        
et dans le controlleur 
    
    $file = $person->getPicture();
    $fileName = $this->get('trombi.file_uploader')->upload($file);
    
Remarque : le code n'est pas parfait,
- oblige à recharger une image à chaque edit
- ne gère pas les images optionnelles par ex. (pour ce faire il faut rajouter un peu de code)

Pour généraliser encore un peu plus et ne pas surcharger les controlleurs, on fait alors appelle aux doctrine listeners

### Doctrine listeners

Creer un fichier EventListener/PersonUploadListener.php
 
    class PersonUploadListener
    {
        private $uploader;
    
        public function __construct(FileUploader $uploader)
        {
            $this->uploader = $uploader;
        }
    
        public function prePersist(LifecycleEventArgs $args)
        {
            $entity = $args->getEntity();
    
            $this->uploadFile($entity);
        }
    
        public function preUpdate(PreUpdateEventArgs $args)
        {
            $entity = $args->getEntity();
    
            $this->uploadFile($entity);
        }
    
        private function uploadFile($entity)
        {
            // upload only works for Product entities
            if (!$entity instanceof Person) {
                return;
            }
    
            $file = $entity->getPicture();
    
            // only upload new files
            if (!$file instanceof UploadedFile) {
                return;
            }
    
            $fileName = $this->uploader->upload($file);
            $entity->setPicture($fileName);
        }
    }
    
tout le code s'execute automatiquement quand les événements prePersist et preUpdate apparaissent
il n'y a plus du tout de code gérant l'upload dans le controlleur

Il suffit de configurer le service suivant 

    trombi.doctrine_person_listener:
        class: TrombiBundle\EventListener\PersonUploadListener
        arguments: ['@trombi.file_uploader']
        tags:
            - { name: doctrine.event_listener, event: prePersist }
            - { name: doctrine.event_listener, event: preUpdate }

Pour la gestion de l'edit, ajouter


    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        //dump($entity);

        if (!$entity instanceof Person) {
            return;
        }
        if ($fileName = $entity->getPicture()) {
            $entity->setPicture(new File($this->uploader->getTargetDir().'/'.$fileName));
        }
    }

Et ajouter au service

     - { name: doctrine.event_listener, event: postLoad }
     
Pour la gestion de la suppression du fichier quand on remove un enregistrement, ajouter

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if (!$entity instanceof Person) {
            return;
        }

        if(is_file($entity->getPicture())) {
            unlink($entity->getPicture());
        }
    }
    
Dans le service 
    
     - { name: doctrine.event_listener, event: preRemove }


Il reste un problème sur l'appelle des nom d'images (car on renvoi ) 

    <?php
     //TrombiBundle/Twig/TwigExtension.php
    
    namespace TrombiBundle\Twig;
    
    class TwigExtension extends \Twig_Extension
    {
    
        public function getName()
        {
            return 'twig_extension';
        }
    
        public function getFilters()
        {
            return [
                new \Twig_SimpleFilter('basename', [$this, 'basenameFilter'])
            ];
        }
    
        /**
         * @var string $value
         * @return string
         */
        public function basenameFilter($value, $suffix = '')
        {
            return basename($value, $suffix);
        }
    }
    
et dans les services

    trombi.twig_extension:
        class: TrombiBundle\Twig\TwigExtension
        public: false
        tags:
            - { name: twig.extension }    
    

    
Solution complète : VichUploadBundle