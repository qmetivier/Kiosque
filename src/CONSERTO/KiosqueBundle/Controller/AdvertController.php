<?php
namespace CONSERTO\KiosqueBundle\Controller;

use CONSERTO\KiosqueBundle\Entity\magazine;
use CONSERTO\KiosqueBundle\Form\magazineType;
use CONSERTO\KiosqueBundle\Form\magazineEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{

    public function formloginAction( Request $request )
    {
        if ($request->getSession()->has('titre')){
            return $this->redirectToRoute('conserto_kiosque_home');
        }
        return $this->render('CONSERTOKiosqueBundle:Advert:login.html.twig');
    }

    /*<!-- controller de l'ecran post Login  -->*/
    public function loginAction( Request $request )
    {
        if($request->isMethod('POST')){
            $username = $_POST['_username'];
            $request->getSession()->set('ldapconn', ldap_connect("192.168.1.2", 389));// host_local: 192.168.1.2 // port_local: 389
            $ldapconn = $request->getSession()->get('ldapconn');
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            $ldapbind = ldap_bind($ldapconn, 'cn=admin,dc=conserto,dc=pro',"Choh2Peci8");
            if ($ldapbind){
                $filter = "(&(objectClass=person)(uid={$username}))";
                $search = ldap_search($ldapconn,'ou=utilisateurs,dc=conserto,dc=pro', $filter);
                if (ldap_parse_result($ldapconn, $search, $error)){
                    $info = ldap_get_entries($ldapconn, $search);
                    if($info['count'] == 0){
                        $request->getSession()->getFlashBag()->add('login', 'Identifiant ou Mot de passe incorrect');
                        return $this->RedirectToRoute('conserto_login');
                    }else{
                        $username = ($info[0]['cn'][0]);
                        try{
                            ldap_bind($ldapconn, "cn=$username,ou=utilisateurs,dc=conserto,dc=pro", $_POST['password']);
                        }catch (\Exception $exception){
                            $request->getSession()->getFlashBag()->add('login', 'Identifiant ou Mot de passe incorrect');
                            return $this->RedirectToRoute('conserto_login');
                        }
                        $role = 0;
                        if (isset($info[0]['businesscategory'])){
                            foreach ($info[0]['businesscategory'] as $item){
                                if ($item === "ADMINISTRATIF"){
                                    $role = 1;
                                    $request->getSession()->set('titre', "ROLE_ADMIN");
                                }
                            }
                        }
                        if ($role != 1 ){$request->getSession()->set('titre', "ROLE_USER");}
                        /* ON se redirige vers la page d'acceuil */
                        return $this->RedirectToRoute('conserto_kiosque_home');
                    }
                }
            }
        }
        return $this->RedirectToRoute('conserto_login');
    }

    /*<!-- controller de l'ecran de transition  -->*/
    public function logoutAction( Request $request )
{
if ($request->getSession()->isStarted()){
$request->getSession()->clear();
}
/* ON se redirige vers la page d'acceuil */
return $this->RedirectToRoute('conserto_login');
}

    /*<!-- controller de la page Home du coté admin et user  -->*/
    public function viewAction( Request $request, $page, $viewmod )
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}
        /* SECURITé ANTI-SPAM POUR LA MODIFICATION ET L'AJOUT DE MAGAZINE */
        $request->getSession()->remove('AntiSpam');

        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('CONSERTOKiosqueBundle:magazine')
        ;

        /* On récupère les 3 derniers magazines enregistrés pour le carousel */
        $carousels = $repository->findBy(
            array(),                  //Critere
            array('date' => 'desc'),  //classement
            3,                        //nombre d'element
            0                         // debut de recuperation
        );

        if (isset($_POST['sorts'])){
            $request->getSession()->set('sorts', $_POST['sorts']);
        }else{$request->getSession()->set('sorts', 'date');}
        if ($request->getSession()->get('sorts') == 'date'){
            $paramSearch = 'desc';
        }else{$paramSearch = 'asc';}
        /* Le nombre limite de magazine affiché sur une page */
        $nbelementparpage = 12;

        /* On récupère les magazines qu'on veut afficher sur la page actuelle */
        $magazines = $repository->findBy(
            array(),                                                              //Critere
            array($request->getSession()->get('sorts') => $paramSearch),          //classement
            $nbelementparpage,                                                    //nombre d'element
            0+$page*$nbelementparpage                                             // debut de recuperation
        );

        /* On récupère le nombre de magazines à affichés */
        $nbDoc = $repository->getNbDocInListe();

        /* On initialise search dans la session */
        if (!$request->getSession()->has('search')) {
            $request->getSession()->set('search', '');
        }
        /* on récupère, si elle existe, la valeur de search et on la sauvegarde dans la session */
        if (isset($_POST['search'])) {
            $request->getSession()->set('search', $_POST['search']);
        }

        /* On initialise la catégorie dans la session */
        if(!$request->getSession()->has('search_categorie')){
            $request->getSession()->set('search_categorie', '');
        }
        /* on récupère, si elle existe, la valeur de la catégorie et on la sauvegarde dans la session */
        if (isset($_POST['search_categorie'])) {
            $request->getSession()->set('search_categorie', $_POST['search_categorie']);
        }


        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('CONSERTOKiosqueBundle:Category')
        ;

        /* on récupère tout les noms des catégories */
        $category = $repository->findAll();

        /* Si une catégorie de recherche est saisie */
        if($request->getSession()->get('search_categorie') != ''){

            /* On récupère l'id de tout les magazines qui appartiennent à cette catégorie */
            $magazine = $repository->getmagazineId($request->getSession()->get('search'),$request->getSession()->get('search_categorie'), $page, $nbelementparpage,$request->getSession()->get('sorts'));
            /* On récupère le nombre de magazines à affichés */
            $nbDoc = $repository->getNbDocInListeWithCategoriesAndSearch($request->getSession()->get('search'),$request->getSession()->get('search_categorie'));
            // On récupère le repository
            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('CONSERTOKiosqueBundle:magazine')
            ;
            /* On vide les magazines qui on été enegistrés */
            unset($magazines);

            /* Pour chaque id de magazine on récupère toute les info de ce magazine et donc le Pdf */
            foreach ($magazine as $key) {
                $magazines[] = $repository->find($key['id']);
            }
        }
        /* Si une recherche est saisie mais que aucune catégorie est saisie */
        else if ($request->getSession()->get('search') != '' && $request->getSession()->get('search_categorie') == '') {
            // On récupère le repository
            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('CONSERTOKiosqueBundle:magazine')
            ;
            /* On récupère les magazines qu'on veut afficher sur la page actuelle */
            $magazines = $repository->getmagazineWithCategoriesAndSearch($request->getSession()->get('search'), $page, $nbelementparpage,$request->getSession()->get('sorts'));
            /* On récupère le nombre de magazines à affichés */
            $nbDoc = $repository->getNbDocInListeWithCategoriesAndSearch($request->getSession()->get('search'));
        }

        /* On calcule le nombre de page qu'il nous faut afin d'affiché tout les magazines */
        $nbPage = ceil($nbDoc/$nbelementparpage)-1;

        /* Si il n'y a pas de magazine a afficher on prépare le message pour l'annoncé à l'utilisateur */
        if ($nbPage < 0) {
            $request->getSession()->getFlashBag()->add('notice', 'Aucun résultat a votre recherche');
            $nbPage = 0;
        }
        if (!isset($magazines)) {
            $magazines = '';
        }
        /* on rentre toute les données dans un tableau pour alléger l'ecriture */
        $temp = array(
            'magazines' => $magazines,
            'carousels' => $carousels,
            'category'  => $category,
            'nbDoc'     => $nbDoc,
            'nb_page'   => $nbPage,
            'page'      => $page,
            'search'    => $request->getSession()->get('search'),
            'search_categorie'  => $request->getSession()->get('search_categorie'),
            'sorts' => $request->getSession()->get('sorts'),
            'viewmod'   => $viewmod,
        );

        /* Si l'administrateur veux avoir un rendu de la page utilisateur */
        if (isset($viewmod)){
            if($viewmod) {
                return $this->render('CONSERTOKiosqueBundle:Advert:preview_admin.html.twig',$temp);
            }
        }

        /* Si la personne est un administrateur ou super_administrateur on affiche le format administrateur */
        if ($request->getSession()->get('titre') != 'ROLE_USER' ) {

            // Le render ne change pas, on passait avant un tableau, maintenant un objet
            return $this->render('CONSERTOKiosqueBundle:Advert:home_admin.html.twig', $temp);

        }
        /* Si la personne est donc un utilisateur on affiche le format utilisateur */
        return $this->render('CONSERTOKiosqueBundle:Advert:home.html.twig', $temp);

    }

    /*<!-- controller de l'ecran d'affichage du magazine coté utilisateur  -->*/
    public function show_userAction( Request $request, $id, $viewmod )
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}

        if (isset($viewmod)){
            if(!$viewmod) {
                if($request->getSession()->get('titre') != 'ROLE_USER'){
                    return $this->RedirectToRoute('conserto_kiosque_doc_admin');
                }
            }
        }
        /* on récupère le magazine qui possède cet Id */
        $em = $this->getDoctrine()->getManager();
        $magazine = $em->getRepository('CONSERTOKiosqueBundle:magazine')->find($id);


        /* Si le magazine n'existe pas on personnalise la page d'erreur */
        if (null === $magazine) {
            $request->getSession()->getFlashBag()->add('notice', 'Le magazine selectionné n\'existe pas .');
            return $this->redirectToRoute('conserto_kiosque_home');
        }

        /* Si tout ce passe bien on affiche la page du doc avec les info de ce magazine */
        return $this->render('CONSERTOKiosqueBundle:Advert:doc_user.html.twig',array(
            'magazine' => $magazine,
            'viewmod'  => $viewmod,
        ));
    }

    /*<!-- controller pour le téléchargement du magazine  -->*/
    public function download_doc_userAction(Request $request, $id)
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}

        /* on récupère le magazine qui possède cet Id */
        $em = $this->getDoctrine()->getManager();
        $magazine = $em->getRepository('CONSERTOKiosqueBundle:magazine')->find($id);

        /*On recupère le nom du Pdf en question */
        $fichier = $magazine->getPdf()->getid().'.'.$magazine->getPdf()->getlink();
        $chemin = "c://wamp64/www/Symfony/web/uploads/pdf/"; // emplacement de votre fichier .pdf


        /*On créer une réponse qui va récupérer le fichier a son emplacement*/
        $response = new Response();
        $response->setContent(file_get_contents($chemin.$fichier));
        $response->headers->set('Content-Type', 'application/force-download'); // modification du content-type pour forcer le téléchargement (sinon le navigateur internet essaie d'afficher le document)
        $response->headers->set('Content-disposition', 'filename='. $magazine->getTitle().'.'.$magazine->getPdf()->getlink());
        /*On retourne cette réponse afin de lancé le téléchargement*/
        return $response;


    }


    /*<!-- controller de l'ecran d'ajout du magazine  -->*/
    public function add_doc_adminAction( Request $request)
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}
        if($request->getSession()->get('titre') != 'ROLE_ADMIN'){
            return $this->RedirectToRoute('conserto_kiosque_home');}
        /* On créer un nouvelle objet de type magazine */
        $advert = new magazine();

        $desc_img = '';
        if (isset($_FILES['img'])) {
            $extension_upload = strtolower(  substr(  strrchr($_FILES['img']['name'], '.')  ,1)  );
            $str = ["-", "_"];
            $name = str_replace($str, " ", substr($_FILES['img']['name'], 0, -4));
            if ($extension_upload == "pdf") {
                $nom = "tmp_pdf.".$extension_upload;
                $resultat = move_uploaded_file($_FILES['img']['tmp_name'], "./uploads/$nom");
                if ($resultat){
                    exec('magick convert "C:\wamp64\www\Symfony\web\uploads\tmp_pdf.pdf[0]" "C:\wamp64\www\Symfony\web\uploads\temp_img.png"');
                    $src_img = "/Symfony/web/uploads/temp_img.png";
                    $desc_img = " le fichier $name est valide ";
                }else{ $desc_img = " le fichier $name est invalide "; }
            }else{ $desc_img = " le fichier $name est invalide "; }
        }

        if (!isset($src_img)) {
            $src_img = "/Symfony/web/img/couverture_mag.jpg";
        }

        /* On récupère le formulaire de création d'un magazine */
        $form = $this->get('form.factory')->create(magazineType::class, $advert);

        /* si le formulaire est envoyé et est valide */
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            /* On lance la suite que si L'AntiSpam est désactivé */
            if(!$request->getSession()->has('AntiSpam')){
                $request->getSession()->set('AntiSpam', 1);
                /* On sauvegarde dans la SQL les infos */
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();
                /* On prépare le message de confirmation d'ajout du magazine */
                $request->getSession()->getFlashBag()->add('notice', 'Ajout du magazine effectué.');
            }
            /* On retourne au home */
            return $this->redirectToRoute('conserto_kiosque_home');
        }


        /* Sinon c'est que le formulaire n'est pas apparue ou qu'il n'est pas valide donc on l'affiche */
        return $this->render('CONSERTOKiosqueBundle:Advert:doc_admin.html.twig',array(
            'form' => $form->createView(),
            'img'  => $src_img,
            'desc_img' => $desc_img,
            'viewmod' => false,
        ));
    }

    /*<!-- controller de l'ecran de modifcation du magazine  -->*/
    public function edit_doc_adminAction( Request $request , $id)
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}
        if($request->getSession()->get('titre') != 'ROLE_ADMIN'){
            return $this->RedirectToRoute('conserto_kiosque_home');}
        /* Si la personne n'est pas un administrateur ou super_administrateur on le redirige vers le format utilisateur */
        if($request->getSession()->get('titre') == 'ROLE_USER'){
            return $this->redirectToRoute('conserto_kiosque_doc_user');
        }
        /* On récupère le magazine en question */
        $em = $this->getDoctrine()->getManager();
        $magazine = $em->getRepository('CONSERTOKiosqueBundle:magazine')->find($id);

        /*On transfert les infos dans advert */
        $advert = $magazine;
        $src_img = "../../../uploads/img/$id.jpg";

        /* On créer le formulaire qu'on pré-remplie avec les infos enregister */
        $form = $this->get('form.factory')->create(magazineEditType::class, $advert);

        /* si le formulaire est envoyé et est valide */
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            /* On lance la suite que si L'AntiSpam est désactivé */
            if(!$request->getSession()->has('AntiSpam')){
                $request->getSession()->set('AntiSpam', 1);
                /* On sauvegarde dans la SQL les infos */
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();
                /* On prépare le message de confirmation de modification du magazine */
                $request->getSession()->getFlashBag()->add('notice', 'Ajout du magazine effectué.');
            }
            /* On retourne au home */
            return $this->redirectToRoute('conserto_kiosque_home');
        }

        /* Sinon c'est que le formulaire n'est pas apparue ou qu'il n'est pas valide donc on l'affiche */
        return $this->render('CONSERTOKiosqueBundle:Advert:doc_admin.html.twig',array(
            'form' => $form->createView(),
            'img'  => $src_img,
            'magazine' => $magazine,
            'viewmod' => false,
        ));
    }

    /*<!-- controller de suppression du magazine  -->*/
    public function delete_doc_adminAction( Request $request , $id)
    {
        if(!$request->getSession()->has('titre')){ return $this->redirectToRoute('conserto_login');}

        if($request->getSession()->get('titre') != 'ROLE_ADMIN'){
            return $this->redirectToRoute('conserto_kiosque_home');
        }

        /* On récupère les infos du magazine */
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('CONSERTOKiosqueBundle:magazine')->find($id);

        /* si le magazine n'existe pas on personnalise la page d'erreur */
        if (null === $advert) {
            $request->getSession()->getFlashBag()->add('notice', 'Le magazine selectionné n\'existe pas .');
            return $this->redirectToRoute('conserto_kiosque_home');
        }
        /* On supprime le magazine */
        $em->remove($advert);
        $em->flush();

        /* On prépare le message de confirmation de suppression du magazine */
        $request->getSession()->getFlashBag()->add('notice', "L'annonce a bien été supprimée.");
        /* On retourne sur le home */
        return $this->redirectToRoute('conserto_kiosque_home');
    }
}
