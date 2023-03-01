function okay(resp) { 
    if (!resp.ok) 
        throw new Error(resp); 
        return resp.text();
 }

 // a2js is less strict than JSON.decode.
function a2js(s) 
{ 
    try {
        return Function('"use strict"; return (' + s + ')')(); 
    }
    catch (x) {
        console.log(s);
        throw {status:'error', message: x.message};
    }
}

function js2a(js) { return JSON.stringify(js); }

function accessible(a, priv)
{
    return a.toLowerCase().split('/[, ]+/').indexOf(priv.toLowerCase()) != -1;
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

  
class FilterSet
{
    fset = [];

    constructor(pattern, defs) {
        pattern.trim().split(/\s+/).forEach(expr => {
            var p = expr.indexOf(':');
            try {
                var re = new RegExp(expr.substr(p+1), 'i');
                if (p == -1) {
                    defs.forEach(attr => {
                        this.fset.push({key: attr, regex: re});
                    });
                }
                else {
                    this.fset.push({key: expr.substr(0,p), regex: re});
                }
            }
            catch(ex) {
                console.log("FilterSet regex error:"+expr)
            }
        });
    }

    match(target) {
        for(var i=0; i<this.fset.length; ++i) {
            var p = this.fset[i];
            if (p.key in target) {
                if (!p.regex.test(target[p.key])) {
                    return false;
                }
            }
        }
        return true;
    }
}

/* Enhanced from: https://codepen.io/trongthanh/pen/rmYBdX */
function vn2us(str) 
{
	return str.trim()
        .toLowerCase()                      // Chuyển hết sang chữ thường
		.normalize('NFD')                   // chuyển chuỗi sang unicode tổ hợp
		.replace(/[\u0300-\u036f]/g, '')    // xóa các ký tự dấu sau khi tách tổ hợp
        .replace(/[đĐ]/g, 'd')              // Thay ký tự đĐ
        .replace(/[^0-9a-z-\s]/g, '')       // Xóa ký tự đặc biệt
	    .replace(/\s+/g, ' ')               // Xóa khoảng trắng
    ;
}

export { postURL, FilterSet, alike, bracket, vn2us }