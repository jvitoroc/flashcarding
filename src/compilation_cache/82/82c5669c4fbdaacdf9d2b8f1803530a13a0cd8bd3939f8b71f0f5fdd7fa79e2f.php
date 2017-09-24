<?php

/* login.html */
class __TwigTemplate_81cce187f6962cb7a421371fe3ce60d79787d73c78afbe73138237ddb613d803 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!doctype html>
<html>
\t<head>
\t\t<title>Login</title>
\t</head>
\t<body>
\t\t<div class=\"wrap\">
\t\t\t<form class=\"login-form\" method=\"post\" action=\"/login\">
\t\t\t\t<div class=\"login-group\">
\t\t\t\t\t<label>Username</label>
\t\t\t\t\t<input type=\"text\" name=\"username\">
\t\t\t\t</div>
\t\t\t\t<div class=\"login-group\">
\t\t\t\t\t<label>Password</label>
\t\t\t\t\t<input type=\"password\" name=\"password\">
\t\t\t\t</div>
\t\t\t</form>
\t\t</div>
\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "login.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "login.html", "C:\\xampp\\htdocs\\flashcarding\\src\\templates\\login.html");
    }
}
