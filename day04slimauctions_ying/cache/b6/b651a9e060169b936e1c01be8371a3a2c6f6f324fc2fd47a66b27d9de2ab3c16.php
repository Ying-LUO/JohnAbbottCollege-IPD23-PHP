<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* master.html.twig */
class __TwigTemplate_c1a79f92a2c94b35c181e08e8c1f22355dcaa51c3b5da018ee91cdf78d430b95 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'head' => [$this, 'block_head'],
            'title' => [$this, 'block_title'],
            'content' => [$this, 'block_content'],
            'footer' => [$this, 'block_footer'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        ";
        // line 4
        $this->displayBlock('head', $context, $blocks);
        // line 8
        echo "    </head>
    <body>
        <div id=\"centeredContent\">
            ";
        // line 11
        $this->displayBlock('content', $context, $blocks);
        // line 13
        echo "            <div id=\"footer\">
                ";
        // line 14
        $this->displayBlock('footer', $context, $blocks);
        // line 17
        echo "            </div>
        </div>
    </body>
</html>";
    }

    // line 4
    public function block_head($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 5
        echo "            <link rel=\"stylesheet\" href=\"/styles.css\"/>  ";
        // line 6
        echo "            <title>";
        $this->displayBlock('title', $context, $blocks);
        echo " - My Webpage</title>
        ";
    }

    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
    }

    // line 11
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 12
        echo "            ";
    }

    // line 14
    public function block_footer($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 15
        echo "                    &copy; Copyright 2011 by <a href=\"http://domain.invalid/\">you</a>.
                ";
    }

    public function getTemplateName()
    {
        return "master.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  97 => 15,  93 => 14,  89 => 12,  85 => 11,  73 => 6,  71 => 5,  67 => 4,  60 => 17,  58 => 14,  55 => 13,  53 => 11,  48 => 8,  46 => 4,  41 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        {% block head %}
            <link rel=\"stylesheet\" href=\"/styles.css\"/>  {# add '/' before styles.css is absolute url, without it is reference url#}
            <title>{% block title %}{% endblock %} - My Webpage</title>
        {% endblock %}
    </head>
    <body>
        <div id=\"centeredContent\">
            {% block content %}
            {% endblock %}
            <div id=\"footer\">
                {% block footer %}
                    &copy; Copyright 2011 by <a href=\"http://domain.invalid/\">you</a>.
                {% endblock %}
            </div>
        </div>
    </body>
</html>", "master.html.twig", "C:\\xampp\\htdocs\\ipd23\\day04slimauctions\\templates\\master.html.twig");
    }
}
