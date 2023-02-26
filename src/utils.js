function okay(resp) { 
    if (!resp.ok) 
        throw new Error(resp); 
        return resp.text();
 }

 // a2js is less strict than JSON.decode.
function a2js(s) 
{ 
    return Function('"use strict"; return (' + s + ')')(); 
}

function js2a(js) { return JSON.stringify(js); }

function accessible(a, priv)
{
    return a.toLowerCase().split('/[, ]+/').indexOf(priv.toLowerCase()) != -1;
}

function bracket(a)
{
    return "\u{00ab}"+a+"\u{00bb}";
}

function alike(a, b)
{
    for (var key in a) {
        if (!(key in b) || a[key] != b[key]) {
                return false;
        }
    }
    return true;
}

function postURL(url, content) 
{
    return fetch(url, {method:'POST', body:JSON.stringify(content)})
        .then(okay)
        .then(a2js)
        .then(resp => {
            if (resp.status === 'okay') {
                return resp;
            }
            else {
                console.log(resp);
                return Promise.reject(resp);
            }
        });
}

function inplace(container, replacement)
{
    while (container.firstChild) {
        container.removeChild(container.firstChild)
    }
    if (replacement) {
        container.appendChild(replacement)
    }
    return container;
}
  
class FilterSet
{
    fset = {};

    constructor(expr, defs) {
	expr.split(/\s+/).forEach(expr => {
            var p = expr.indexOf(':');
            var re = new RegExp(expr.substr(p+1), 'i');
            if (p == -1) {
		defs.forEach(attr => {
                    this.fset[attr] = re;
		});
            }
            else {
                this.fset[expr.substr(0,p)] = re;
            }
        });
    }

    match(target) {
        for(var attr in this.fset) {
            if (attr in target) {
                if (this.fset[attr].test(target[attr])) {
                    return true;
                }
            }
        }
        return false;
    }

}

export { postURL, FilterSet, alike, bracket }