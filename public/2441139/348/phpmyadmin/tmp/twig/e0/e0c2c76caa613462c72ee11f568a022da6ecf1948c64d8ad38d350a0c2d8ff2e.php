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

/* server/engines/index.twig */
class __TwigTemplate_b960e4648bfb5576bdd153859b50d7db0c38ee65efa583153b61c71751fee33a extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<div class=\"container-fluid\">
  <div class=\"row\">
  <h2>
    ";
        // line 4
        echo \PhpMyAdmin\Html\Generator::getImage("b_engine");
        echo "
    ";
        // line 5
        echo _gettext("Storage engines");
        // line 6
        echo "  </h2>
  </div>

  <div class=\"table-responsive\">
    <table class=\"table table-light table-striped table-hover w-auto\">
      <thead class=\"thead-light\">
        <tr>
          <th scope=\"col\">";
        // line 13
        echo _gettext("Storage Engine");
        echo "</th>
          <th scope=\"col\">";
        // line 14
        echo _gettext("Description");
        echo "</th>
        </tr>
      </thead>
      <tbody>
        ";
        // line 18
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["engines"] ?? null));
        foreach ($context['_seq'] as $context["engine"] => $context["details"]) {
            // line 19
            echo "          <tr class=\"";
            // line 20
            echo (((((($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4 = $context["details"]) && is_array($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4) || $__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4 instanceof ArrayAccess ? ($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4["Support"] ?? null) : null) == "NO") || ((($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144 = $context["details"]) && is_array($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144) || $__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144 instanceof ArrayAccess ? ($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144["Support"] ?? null) : null) == "DISABLED"))) ? (" disabled") : (""));
            echo "
            ";
            // line 21
            echo ((((($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b = $context["details"]) && is_array($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b) || $__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b instanceof ArrayAccess ? ($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b["Support"] ?? null) : null) == "DEFAULT")) ? (" marked") : (""));
            echo "\">
            <td>
              <a rel=\"newpage\" href=\"";
            // line 23
            echo PhpMyAdmin\Url::getFromRoute(("/server/engines/" . $context["engine"]));
            echo "\">
                ";
            // line 24
            echo twig_escape_filter($this->env, (($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002 = $context["details"]) && is_array($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002) || $__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002 instanceof ArrayAccess ? ($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002["Engine"] ?? null) : null), "html", null, true);
            echo "
              </a>
            </td>
            <td>";
            // line 27
            echo twig_escape_filter($this->env, (($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4 = $context["details"]) && is_array($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4) || $__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4 instanceof ArrayAccess ? ($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4["Comment"] ?? null) : null), "html", null, true);
            echo "</td>
          </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['engine'], $context['details'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 30
        echo "      </tbody>
    </table>
  </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "server/engines/index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  102 => 30,  93 => 27,  87 => 24,  83 => 23,  78 => 21,  74 => 20,  72 => 19,  68 => 18,  61 => 14,  57 => 13,  48 => 6,  46 => 5,  42 => 4,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "server/engines/index.twig", "/var/www/html/bapiLaravel/public/2441139/348/phpmyadmin/templates/server/engines/index.twig");
    }
}
