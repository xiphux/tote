</div>
</div>
</div>

{if $showattr}
<div id="attrFooter">
Football Pool by <span id="contact">Chris Han</span>
{if $bitcoin}
<br />
Donate: <a href="bitcoin:{$bitcoin}">{$bitcoin}</a>
{/if}
</div>
<script language="javascript" type="text/javascript">
var b1 = ['christopher', 'f', 'han'].join('.');
var a2 = Math.pow(2,6);
var a4 = 'gmail.com';
var b5 = b1 + String.fromCharCode(a2) + a4;
document.getElementById('contact').innerHTML = '<a href=' + 'mai' + 'lto' + ':' + b5 + '>Chris Han</a>'
</script>
{/if}

</body>
</html>
