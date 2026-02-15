<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('calculator');

// Output calculator form
?>
<script type="text/javascript">
    function calc(A) {
        var gb = document.sizes.gb.value;
        var mb = document.sizes.mb.value;
        var kb = document.sizes.kb.value;
        var b = document.sizes.Bytee.value;
        
        if (A == "gb") {
            document.sizes.mb.value = gb;
            document.sizes.mb.value *= "1024";
            document.sizes.kb.value = gb;
            document.sizes.kb.value *= "1024";
            document.sizes.kb.value *= "1024";
            document.sizes.Bytee.value = gb;
            document.sizes.Bytee.value *= "1024";
            document.sizes.Bytee.value *= "1024";
            document.sizes.Bytee.value *= "1024";
        } else if (A == "mb") {
            document.sizes.gb.value = mb;
            document.sizes.gb.value /= "1024";
            document.sizes.kb.value = mb;
            document.sizes.kb.value *= "1024";
            document.sizes.Bytee.value = mb;
            document.sizes.Bytee.value *= "1024";
            document.sizes.Bytee.value *= "1024";
        } else if (A == "kb") {
            document.sizes.gb.value = kb;
            document.sizes.gb.value /= "1024";
            document.sizes.gb.value /= "1024";
            document.sizes.mb.value = kb;
            document.sizes.mb.value /= "1024";
            document.sizes.Bytee.value = kb;
            document.sizes.Bytee.value *= "1024";
        } else if (A == "Bytee") {
            document.sizes.gb.value = b;
            document.sizes.gb.value /= "1024";
            document.sizes.gb.value /= "1024";
            document.sizes.gb.value /= "1024";
            document.sizes.mb.value = b;
            document.sizes.mb.value /= "1024";
            document.sizes.mb.value /= "1024";
            document.sizes.kb.value = b;
            document.sizes.kb.value /= "1024";
        }
    }
</script>
<form name="sizes">
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">
            <?php echo escape_html($Language[1] ?? 'Size Calculator'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1">            
            <table border="0" width="100%" cellspacing="5" cellpadding="2">
                <tr>
                    <td width="6%" align="right">GB&nbsp;</td>
                    <td width="20%">&nbsp;<input type="text" name="gb" size="20" /></td>
                    <td width="44%">&nbsp;<input onclick="javascript:calc('gb')" type="button" value="<?php echo escape_attr($Language[2] ?? 'Calculate'); ?>" /></td>
                </tr>
                <tr>
                    <td width="6%" align="right">MB&nbsp;</td>
                    <td width="20%">&nbsp;<input type="text" name="mb" size="20" /></td>
                    <td width="44%">&nbsp;<input onclick="javascript:calc('mb')" type="button" value="<?php echo escape_attr($Language[3] ?? 'Calculate'); ?>" /></td>
                </tr>
                <tr>
                    <td width="6%" align="right">KB&nbsp;</td>
                    <td width="20%">&nbsp;<input type="text" name="kb" size="20" /></td>
                    <td width="44%">&nbsp;<input onclick="javascript:calc('kb')" type="button" value="<?php echo escape_attr($Language[4] ?? 'Calculate'); ?>" /></td>
                </tr>
                <tr>
                    <td width="6%" align="right">Byte&nbsp;</td>
                    <td width="20%">&nbsp;<input type="text" name="Bytee" size="20" /></td>
                    <td width="44%">&nbsp;<input onclick="javascript:calc('Bytee')" type="button" value="<?php echo escape_attr($Language[5] ?? 'Calculate'); ?>" /></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
