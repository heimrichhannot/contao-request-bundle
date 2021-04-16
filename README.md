
![](https://img.shields.io/packagist/v/heimrichhannot/contao-request-bundle.svg)
![](https://img.shields.io/packagist/l/heimrichhannot/contao-request-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-request-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-request-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-request-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-request-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-request-bundle)

# Request Bundle

Contao uses it own `Input` class, that check the request for $_GET, $_POST and more parameters.
This is done directly on $_GET, $_POST Server Parameters and for Tests it is not possible to simulate the HTTP-Server.
Here `HeimrichHannot\Request` put on and provide the sumilation of your own HTTP-Server object with help of `symfony/http-foundation`.

## Technical instruction

Use the following alternatives for contao `Input` or `Environment` calls

Contao | Request
---- | -----------
`\Input::get($strKey)` | `\Contao\System->getContainer()->get('huh.request')->getGet($strKey)`
`\Input::post($strKey)` | `\Contao\System->getContainer()->get('huh.request')->getPost($strKey)`
`\Input::postHtml($strKey)` | `\Contao\System->getContainer()->get('huh.request')->getPostHtml($strKey)`
`\Input::postRaw($strKey)` | `\Contao\System->getContainer()->get('huh.request')->getPostRaw($strKey)`
`\Input::setPost($strKey, $varValue)` | `\Contao\System->getContainer()->get('huh.request')->setPost($strKey, $varValue)`
`\Input::setGet($strKey, $varValue)` | `\Contao\System->getContainer()->get('huh.request')->setGet($strKey, $varValue)`
`isset($_GET[$strKey])` | `\Contao\System->getContainer()->get('huh.request')->hasGet($strKey)`
`isset($_POST[$strKey])` | `\Contao\System->getContainer()->get('huh.request')->hasPost($strKey)`
`\Environment::get('isAjaxRequest')` | `\Contao\System->getContainer()->get('huh.request')->isXmlHttpRequest()`


## Insert tags

For convenience we provide insert tags for some request method parameters.

**CAUTION: If you use the insert tags in SQL-Query Context, be sure that you escape the insert tag values by using e.g. `prepare('field=?')->execute('{{request_get::auto_item}}')`**


Insert tag | Description
--- | --------- 
`{{request_get::*}}` | This tag will be replaced with the XSS protected value of the query parameter (replace * with the name of the query parameter, e.g. `auto_item`)
`{{request_post::*}}` | This tag will be replaced with the XSS protected value of the post parameter (replace * with the name of the post parameter, e.g. `FORM_SUBMIT`)
