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

/* newauction.html.twig */
class __TwigTemplate_d49c4adfdcdd45ca9a866c5bad76cef6eb03b5445e19c17f19857f4b3db68c6e extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "master.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("master.html.twig", "newauction.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo "New auction";
    }

    // line 5
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        echo "
<h1>New Auction</h1>

";
        // line 9
        if (($context["errorList"] ?? null)) {
            // line 10
            echo "<ul>
    ";
            // line 11
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["errorList"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["error"]) {
                // line 12
                echo "        <li>";
                echo twig_escape_filter($this->env, $context["error"], "html", null, true);
                echo "</li>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 14
            echo "</ul>
";
        }
        // line 16
        echo "
<form method=\"POST\">
    Description: <textarea name=\"itemDescription\">{ v.itemDescription}</textarea><br>
    <span>Image upload - todo</span>
    Name: <input  type=\"text\" name=\"sellersName\" value=\"";
        // line 20
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["v"] ?? null), "sellersName", [], "any", false, false, false, 20), "html", null, true);
        echo "\" ><br>
    Email: <input  type=\"email\" name=\"sellerEmail\" value=\"";
        // line 21
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["v"] ?? null), "sellerEmail", [], "any", false, false, false, 21), "html", null, true);
        echo "\"><br>
    Initial Bid Price: <input  type=\"text\" name=\"lastBidPrice\" value=\"";
        // line 22
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["v"] ?? null), "lastBidPrice", [], "any", false, false, false, 22), "html", null, true);
        echo "\" ><br>
    <input  type=\"submit\" value=\"Create Auction\" ><br>
</form>

";
    }

    public function getTemplateName()
    {
        return "newauction.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  99 => 22,  95 => 21,  91 => 20,  85 => 16,  81 => 14,  72 => 12,  68 => 11,  65 => 10,  63 => 9,  58 => 6,  54 => 5,  47 => 3,  36 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends \"master.html.twig\" %}

{% block title %}New auction{% endblock %}

{% block content %}

<h1>New Auction</h1>

{% if errorList %}
<ul>
    {% for error in errorList %}
        <li>{{ error }}</li>
    {% endfor %}
</ul>
{% endif %}

<form method=\"POST\">
    Description: <textarea name=\"itemDescription\">{ v.itemDescription}</textarea><br>
    <span>Image upload - todo</span>
    Name: <input  type=\"text\" name=\"sellersName\" value=\"{{ v.sellersName }}\" ><br>
    Email: <input  type=\"email\" name=\"sellerEmail\" value=\"{{ v.sellerEmail }}\"><br>
    Initial Bid Price: <input  type=\"text\" name=\"lastBidPrice\" value=\"{{ v.lastBidPrice }}\" ><br>
    <input  type=\"submit\" value=\"Create Auction\" ><br>
</form>

{% endblock content %}", "newauction.html.twig", "C:\\xampp\\htdocs\\ipd23\\day04slimauctions\\templates\\newauction.html.twig");
    }
}
