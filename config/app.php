<?php

return array(
    // Module principal
    'defaultModule' => 'App',

    // Liste des modules chargés
    'modules' => array(
        'Oft',          // Eléments essentiels
        'Oft\Ihm',      // Styles par défaut
        'Oft\Admin',    // IHM d'administration
        //'Oft\Gassi',  // Connexion via le Gassi
        //'Oft\Siu',    // Connexion via le SIU
    ),

    // Les middlewares seront appellés par la méthode Oft\Application::run() dans l'ordre défini ci dessous.
    // Chaque middleware est responsable de l'appel du middleware suivant.
    'middlewares' => array(
        // Actions réalisées :
        // - Récupère le routeur du ServiceLocator
        // - Récupère les informations en provenance de l'URL courante
        // - Définit currentRoute & currentRouteParams dans l'objet Request
        // - Appelle le middleware suivant
        'route' => 'Oft\Mvc\Middleware\Route',

        // Actions réalisées :
        // - Récupère l'identité de l'utilisateur
        // - Teste les règles d'ACL (concordance ressource MVC et rôle utilisateur)
        // - Si l'accès n'est pas autorisé et l'utilisateur est GUEST : redirection 302 vers /auth/login
        // - Si l'accès n'est pas autorisé et l'utilisateur connecté : modification de la route vers la page d'erreur
        // - Appelle le middleware suivant
        'acl' => 'Oft\Mvc\Middleware\Acl',

        // Actions réalisées :
        // - Appelle le middleware suivant
        // - Si l'option disableLayout est définie : retourne au middleware précédent
        // - Gère les valeurs par défaut pour layout & layoutPath
        // - Créé un LayoutModel avec la clé 'content' définie à la réponse HTML en cours
        // - Effectue un rendu en utilisant le DirectResolver
        'layout' => 'Oft\Mvc\Middleware\Layout',

        // Actions réalisées :
        // - Appelle le middleware suivant et capture la sortie
        // - Défini le template par défaut
        // - Effectue le rendu du ViewModel si l'option 'disableRendering' est false ou non définie
        // - Ajoute la sortie capturée au résultat
        // - Définit le contenu de la réponse HTTP
        'render' => 'Oft\Mvc\Middleware\Render',

        // Actions réalisées :
        // - Récupère la fabrique de contrôleurs depuis le ServiceLocator
        // - Récupère le contrôleur (callable)
        // - Initialise le contrôleur
        // - Appelle l'action avec les paramètres et récupére le résultat (Result)
        // - Si Result est de type :
        //   - ViewModel : utilise le résultat comme ViewModel (sinon un ViewModel est créé)
        //   - array : défini le tableau comme variables du ViewModel (comme ZF2)
        //   - bool : défini l'option 'disableRendering' à la valeur !Result
        //   - sinon : défini la clé 'content' du ViewModel avec le résultat
        //
        // - Si une exception est levée :
        //   - RedirectException : lève à nouveau l'exception (Oft\Mvc\App la gère comme une redirection HTTP)
        //   - NotFoundException : utilise $config['notFoundRoute'] comme prochaine route
        //   - ForwardException : utilise la route définie dans l'exception comme prochaine route
        //   - Autre : utilise $config['errorRoute'] comme prochaine route
        //   -> Toutes ces exceptions sont gérées comme un appel récursif (sauf RedirectException)
        'dispatch' => 'Oft\Mvc\Middleware\Dispatch'
    )
);
