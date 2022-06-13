
(c) Gaetan Vigneron

---
![bear](readme-cover.jpg?raw=true)

# SYJSJsBundle 
<p>
Ce bundle comporte des commandes symfony qui permettent d'exporter en json les entités ORM et les formulaires depuis n'importe quel projet 'symfony 6' . Notamment pour les exploiter en Javascript
</p>

## Command : Entity 
```
php bin/console js:entity App\Entity\User --type=yes
```

## Command : Form
```
php bin/console js:form App\Form\UserInscription 
```


## Installation 


### Composer

Le repo git 

```javascript
   "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/gaetanV/symfony_javascript.git"
        }
    ],
```

La version du release  

```javascript
   "require": {
        ...
        "gaetanv/symfony-javascript" : "*"
    },
```

### Config

Ajouté le bundle au fichier bundles.php

```javascript
return [
    ...
    SYJS\JsBundle\SYJSJsBundle::class => ['all' => true]
];

```
Les parametres obligatoire dans votre repertoire config/packages/syjs_js.yaml

```javascript
syjs_js:
    languages: ["fr",'en']
```

<b>Vous êtes prêts à lancer les commandes</b>