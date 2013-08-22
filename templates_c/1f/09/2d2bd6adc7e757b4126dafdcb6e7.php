<?php

/* workorder.html */
class __TwigTemplate_1f092d2bd6adc7e757b4126dafdcb6e7 extends Twig_Template
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
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
<link rel=\"stylesheet\" href=\"css/workorder/pdf.css\" type=\"text/css\">
<link rel=\"stylesheet\" href=\"css/workorder/print.css\" type=\"text/css\">
</head>
<body>
    <table cellspacing=\"0\" cellpadding=\"0\" class=\"workorder\">
        <tr>
            <td colspan=\"15\" class=\"no-border\"><h2>BORANG ADUAN KEROSAKAN / ARAHAN KERJA (WORK ORDER)</h2></td>
            <td nowrap=\"nowrap\" class=\"no-border\" style=\"page-break-inside: avoid\">
                <table class=\"inner-table\">
                    <tr>
                        <td><img align=\"left\" src=\"images/uitm_logo.png\" width=\"140\"></td>
                        <td><img align=\"left\"  src=\"images/alammedik_logo.png\" width=\"140\"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\" class=\"no-border\">Tandakan <input disabled=\"disabled\" type=\"checkbox\" checked=\"checked\" class=\"chk-demo\"> atau potong mana-mana yang tidak berkenaan</td>
        </tr>
        <tr>
            <td colspan=\"16\" class=\"no-border\">&nbsp;</td>
        </tr>
        <tr>
            <td nowrap=\"nowrap\" colspan=\"16\">
                <table cellspacing=\"0\" cellpadding=\"0\"  class=\"inner-table bordered thick-border\">
                    <tr>
                        <td class=\"gray center row-heading1 bordered thick-border-right\">Tarikh Aduan</td>
                        <td class=\"gray center row-heading1 bordered\">Waktu</td>
                        <td nowrap=\"nowrap\" rowspan=\"3\" class=\"gray bordered thick-border v-center\">
                            <input type=\"checkbox\" ";
        // line 34
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        if ($this->getAttribute($_data_, "is_preventive")) {
            echo "checked=\"checked\"";
        }
        echo " /> Preventive
                            <br>
                            <input type=\"checkbox\" ";
        // line 36
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        if ($this->getAttribute($_data_, "is_corrective")) {
            echo "checked=\"checked\"";
        }
        echo "/> Kerosakan
                        </td>
                        <td colspan=\"19\" class=\"gray center row-heading1 bordered thick-border\">No Aduan</td>
                    </tr>
                    <tr>
                        <td rowspan=\"2\" class=\"gray bordered center thick-border \"><strong>";
        // line 41
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_data_, "report_date"), "html", null, true);
        echo "</strong></td>
                        <td rowspan=\"2\" class=\"gray bordered center thick-border bordered-bottom\"><strong>";
        // line 42
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_data_, "report_time"), "html", null, true);
        echo "</strong></td>
                        <td class=\"gray center wo-no bordered\">";
        // line 43
        if (isset($context["site_arr"])) { $_site_arr_ = $context["site_arr"]; } else { $_site_arr_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_site_arr_, 0, array(), "array"), "html", null, true);
        echo "</td>
                        <td class=\"gray center wo-no bordered\">";
        // line 44
        if (isset($context["site_arr"])) { $_site_arr_ = $context["site_arr"]; } else { $_site_arr_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_site_arr_, 1, array(), "array"), "html", null, true);
        echo "</td>
                        ";
        // line 45
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(range(0, 4));
        foreach ($context['_seq'] as $context["_key"] => $context["x"]) {
            // line 46
            echo "                            <td width=\"16\" class=\"gray center wo-no bordered\">";
            if (isset($context["tems_no_arr"])) { $_tems_no_arr_ = $context["tems_no_arr"]; } else { $_tems_no_arr_ = null; }
            if (isset($context["x"])) { $_x_ = $context["x"]; } else { $_x_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_tems_no_arr_, $_x_, array(), "array"), "html", null, true);
            echo "</td>
                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['x'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 48
        echo "                        <td class=\"gray center wo-no bordered\">-</td>
                        <td class=\"gray center wo-no bordered\">";
        // line 49
        if (isset($context["year_arr"])) { $_year_arr_ = $context["year_arr"]; } else { $_year_arr_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_year_arr_, 0, array(), "array"), "html", null, true);
        echo "</td>
                        <td class=\"gray center wo-no bordered\">";
        // line 50
        if (isset($context["year_arr"])) { $_year_arr_ = $context["year_arr"]; } else { $_year_arr_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_year_arr_, 1, array(), "array"), "html", null, true);
        echo "</td>
                        <td class=\"gray center wo-no bordered\">-</td>
                        <td class=\"gray center wo-no bordered\">B</td>
                        <td class=\"gray center wo-no bordered\">E</td>
                        <td class=\"gray center wo-no bordered\">M</td>
                        ";
        // line 55
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(range(0, 4));
        foreach ($context['_seq'] as $context["_key"] => $context["x"]) {
            // line 56
            echo "                            <td class=\"gray center wo-no ";
            if (isset($context["x"])) { $_x_ = $context["x"]; } else { $_x_ = null; }
            if (($_x_ < 4)) {
                echo "bordered";
            } else {
                echo "bordered right-bordered thick-border";
            }
            echo "\">";
            if (isset($context["wo_no_arr"])) { $_wo_no_arr_ = $context["wo_no_arr"]; } else { $_wo_no_arr_ = null; }
            if (isset($context["x"])) { $_x_ = $context["x"]; } else { $_x_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_wo_no_arr_, $_x_, array(), "array"), "html", null, true);
            echo "</td>
                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['x'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 58
        echo "                    </tr>
                    <tr>
                        <td colspan=\"7\" class=\"gray bordered thick-border bordered-bottom\">Nama Pengadu</td>
                        <td class=\"gray data-sep bordered thick-border bordered-bottom\">:</td>
                        <td colspan=\"11\" class=\"gray bordered thick-border\"><strong>";
        // line 62
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "reporter")), "html", null, true);
        echo "</strong></td>
                    </tr>
                    <tr>
                        <td colspan=\"3\">
                            <table class=\"inner-table\">
                                <tr>
                                    <td nowrap=\"nowrap\">Jabatan</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 70
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "department")), "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">Lokasi</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 75
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "location")), "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">TEMS No</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 80
        if (isset($context["tems_no"])) { $_tems_no_ = $context["tems_no"]; } else { $_tems_no_ = null; }
        echo twig_escape_filter($this->env, $_tems_no_, "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">Status Peralatan</td>
                                    <td class=\"data-sep\">:</td>
                                    <td>
                                        <label class=\"inline\">
                                            <input type=\"checkbox\" /> Kontrak
                                        </label>

                                        <label class=\"inline\">
                                            <input type=\"checkbox\" /> Bukan Kontrak
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">Butiran Aduan</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 98
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo nl2br(twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "description")), "html", null, true));
        echo "</strong></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan=\"19\">
                            <table width=\"100%\" class=\"inner-table\">
                                <tr>
                                    <td nowrap=\"nowrap\">Peralatan</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 107
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "remarks")), "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">Jenama / Model</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 112
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "model")), "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td nowrap=\"nowrap\">No Siri</td>
                                    <td class=\"data-sep\">:</td>
                                    <td><strong>";
        // line 117
        if (isset($context["data"])) { $_data_ = $context["data"]; } else { $_data_ = null; }
        echo twig_escape_filter($this->env, stripslashes($this->getAttribute($_data_, "serialno")), "html", null, true);
        echo "</strong></td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" nowrap=\"nowrap\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" nowrap=\"nowrap\" class=\"center bordered thick-border no-bottom-border\">**Pengesahan Kehadiran Kontraktor Tangani Kerosakan</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" nowrap=\"nowrap\" class=\"bordered thick-border signature no-top-border no-bottom-border\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" nowrap=\"nowrap\" class=\"bordered thick-border signature-desc\">Tandatangan Pengadu, Tarikh &amp; Cop Rasmi</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\" class=\"center row-heading1 gray bordered thick-border\">TINDAKAN</td>
        </tr>
        <tr>
            <td colspan=\"16\">
                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" class=\"inner-table bordered thick-border no-top-border\">
                    <tr>
                        <td colspan=\"8\" class=\"bordered thick-border center\">Tarikh Siasatan</td>
                        <td colspan=\"4\" class=\"bordered thick-border center\">Waktu</td>
                        <td rowspan=\"3\" class=\"bordered thick-border v-center\">
                            <label><input type=\"checkbox\" /> AM</label>
                            <br>
                            <label><input type=\"checkbox\" /> PM</label>
                        </td>
                        <td rowspan=\"3\" class=\"bordered v-center\">
                            <label><input type=\"checkbox\" /> Jangka Mula Kerja</label>
                            <br>
                            <label><input type=\"checkbox\" /> Jangka Siap Kerja</label>
                        </td>
                        <td rowspan=\"3\" width=\"144\" class=\"no-pad\">
                            <table class=\"inner-table\">
                                <tr>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid thick-border-right\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right border-bottom-solid thick-border-right\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                                    <td class=\"empty-box thick-border dotted-border-right thick-border-right\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                        <td rowspan=\"3\" class=\"bordered\"><u>Siasasatan Oleh </u>:</td>
                    </tr>
                    <tr>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid thick-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right border-bottom-solid thick-border-right\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right thick-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right\">&nbsp;</td>
                        <td class=\"empty-box thick-border dotted-border-right thick-border-right\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\">
                <table class=\"inner-table bordered thick-border no-bottom-border\">
                    <tr>
                        <td width=\"33%\" class=\"bordered no-top-border no-bottom-border thick-border\"><u>Alam Medik Sdn Bhd </u>:</td>
                        <td width=\"33%\" class=\"bordered no-top-border no-bottom-border thick-border\"><u>Bahagian Pengurusan Fasiliti</u></td>
                        <td width=\"33%\" class=\"bordered no-top-border no-bottom-border thick-border\"><u>Ulasan</u></td>
                    </tr>
                    <tr>
                        <td class=\"empty-content bordered no-bottom-border no-top-border thick-border\">&nbsp;</td>
                        <td class=\"empty-content bordered no-bottom-border no-top-border thick-border\">&nbsp;</td>
                        <td class=\"empty-content bordered no-bottom-border no-top-border thick-border\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\">
                <table class=\"inner-table bordered gray thick-border\">
                    <tr>
                        <td colspan=\"6\" class=\"bordered thick-border row-heading1\">Tindakan Pembaikian Seterusnya (Jika Ada) :</td>
                    </tr>
                    <tr>
                        <td class=\"bordered thick-border-bottom row-heading1\">No</td>
                        <td class=\"bordered thick-border-bottom row-heading1\">Keterangan</td>
                        <td class=\"bordered thick-border-bottom row-heading1\">Kuantiti</td>
                        <td class=\"bordered thick-border-bottom row-heading1\">Harga Seunit</td>
                        <td class=\"bordered thick-border-bottom row-heading1\">Jumlah Harga</td>
                        <td class=\"bordered thick-border-bottom row-heading1\">Jaminan</td>
                    </tr>
                    ";
        // line 253
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(range(0, 7));
        foreach ($context['_seq'] as $context["_key"] => $context["x"]) {
            // line 254
            echo "                        <tr>
                            <td class=\"bordered\">&nbsp;</td>
                            <td class=\"bordered\">&nbsp;</td>
                            <td class=\"bordered\">&nbsp;</td>
                            <td class=\"bordered\">&nbsp;</td>
                            <td class=\"bordered\">&nbsp;</td>
                            <td class=\"bordered\">&nbsp;</td>
                        </tr>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['x'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 263
        echo "                    <tr>
                        <td colspan=\"4\" class=\"bordered thick-border\">Arahan Kerja Oleh /<br />
                            Setuju Harga Pembaikian<br />
                            (BAHAGIAN PENGURUSAN FASILITI UiTM)
                        </td>
                        <td colspan=\"2\" class=\"bordered thick-border\">
                            <p>Kerja dilakukan Oleh:</p>
                            <br>
                            <label><input type=\"checkbox\" /> ALAM MEDIK SDN BHD</label>
                            <br>
                            <label><input type=\"checkbox\" /> WARRANTI PRINSIPAL PERALATAN</label>
                            <br>
                            <table class=\"inner-table other-input\">
                                <tr>
                                    <td class=\"dotted-top\">&nbsp;</td>
                                </tr>
                            </table>
                            <br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\" class=\"no-pad no-top-border\">
                <table class=\"inner-table bordered thick-border  no-top-border\">
                    <tr>
                        <td class=\"center row-heading1 pad-4\">PENGESAHAN KERJA</h2></td>
                    </tr>
                    <tr>
                        <td class=\"bordered pad-4\">Kerja-kerja tersebut telah disiapkan pada :</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=\"16\">
                <table class=\"inner-table bordered thick-border no-top-border\">
                    <tr>
                        <td width=\"33%\" class=\"center row-heading1 thick-border-right\">ALAM MEDIK SDN BHD</td>
                        <td width=\"33%\" class=\"center thick-border-right row-heading1\">PENGGUNA</td>
                        <td width=\"33%\" class=\"center row-heading1\">BAHAGIAN PENGURUSAN FASILITI</td>
                    </tr>
                    <tr>
                        <td class=\"bordered thick-border-right empty-content\">&nbsp;</td>
                        <td class=\"bordered thick-border-right\">&nbsp;</td>
                        <td class=\"bordered\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "workorder.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  392 => 263,  378 => 254,  374 => 253,  234 => 117,  225 => 112,  216 => 107,  203 => 98,  181 => 80,  172 => 75,  163 => 70,  151 => 62,  145 => 58,  127 => 56,  123 => 55,  114 => 50,  109 => 49,  106 => 48,  95 => 46,  91 => 45,  86 => 44,  81 => 43,  76 => 42,  71 => 41,  60 => 36,  52 => 34,  17 => 1,);
    }
}
