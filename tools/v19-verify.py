"""V19 SEO & GA4 automated verifier using urllib + re (no browser/Playwright)."""
from pathlib import Path
from http.cookiejar import CookieJar
import urllib.request, urllib.parse, urllib.error, socket, json, re, html, time

resolve = socket.getaddrinfo
socket.getaddrinfo = lambda host, *a, **kw: resolve('127.0.0.1' if host == 'localhost' else host, *a, **kw)

BASE = 'http://localhost:8082/wordpress'
ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT.parent / 'diseno_minikids/v5/verificacion-V19.json'

cj = CookieJar()
opener = urllib.request.build_opener(
    urllib.request.ProxyHandler({}),
    urllib.request.HTTPCookieProcessor(cj),
)


def fetch(url, data=None, headers=None):
    t0 = time.perf_counter()
    hdrs = {'User-Agent': 'MiniKids-V19-Verify/1.0'}
    if headers:
        hdrs.update(headers)
    req = urllib.request.Request(url, data=data, headers=hdrs)
    try:
        resp = opener.open(req, timeout=25)
        body = resp.read().decode('utf-8', 'replace')
        return resp.status, resp.geturl(), body, time.perf_counter() - t0
    except urllib.error.HTTPError as e:
        body = e.read().decode('utf-8', 'replace')
        return e.code, e.geturl(), body, time.perf_counter() - t0


def add_cart_item_for_checkout(product_id=474):
    payload = urllib.parse.urlencode({'product_id': str(product_id), 'quantity': '1'}).encode()
    st, _, body, _ = fetch(f'{BASE}/?wc-ajax=add_to_cart', data=payload)
    return st == 200


def cleanup_cart():
    try:
        st, _, body, _ = fetch(f'{BASE}/wp-json/wc/store/v1/cart')
        if st == 200:
            data = json.loads(body)
            # Get nonce from cart page or store endpoint headers
            req = urllib.request.Request(f'{BASE}/wp-json/wc/store/v1/cart', headers={'User-Agent': 'MiniKids-V19-Verify/1.0'})
            with opener.open(req, timeout=15) as r:
                nonce = r.headers.get('Nonce')
            if nonce:
                for it in data.get('items', []):
                    rm = urllib.request.Request(
                        f'{BASE}/wp-json/wc/store/v1/cart/remove-item',
                        data=json.dumps({'key': it['key']}).encode(),
                        headers={'Content-Type': 'application/json', 'Nonce': nonce, 'User-Agent': 'MiniKids-V19-Verify/1.0'},
                    )
                    opener.open(rm, timeout=15).read()
    except Exception:
        pass


def check_page(name, url, expect_noindex=False, expected_types=None, expect_status=200, check_ga_debug=False):
    status, final_url, raw, elapsed = fetch(url)
    issues = []

    if status != expect_status:
        issues.append(f'HTTP {status} != {expect_status} (final={final_url})')

    titles = [html.unescape(re.sub(r'\s+', ' ', t)).strip() for t in re.findall(r'<title[^>]*>(.*?)</title>', raw, re.S | re.I)]
    if len(titles) != 1:
        issues.append(f'Expected 1 <title>, found {len(titles)}')
    title = titles[0] if titles else ''
    if not title or len(title) > 60:
        issues.append(f'Title length out of range (1..60): {len(title)} ({title!r})')
    if 'MiniKids Peru' in title:
        issues.append(f'Title contains unaccented Peru: {title!r}')

    descs = re.findall(r'<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']', raw, re.I)
    if len(descs) != 1:
        issues.append(f'Expected 1 meta description, found {len(descs)}')
    desc = html.unescape(descs[0]).strip() if descs else ''
    if not (120 <= len(desc) <= 158):
        issues.append(f'Meta description length out of range (120..158): {len(desc)}')

    canonicals = re.findall(r'<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']*)["\']', raw, re.I)
    if expect_status == 200:
        if len(canonicals) != 1:
            issues.append(f'Expected 1 canonical, found {len(canonicals)}')
        elif '?' in canonicals[0]:
            issues.append(f'Canonical has query string: {canonicals[0]}')

    hreflangs = re.findall(r'<link[^>]+rel=["\']alternate["\'][^>]+hreflang=["\']([^"\']+)["\']', raw, re.I)
    if expect_status == 200 and not ({'es-pe', 'x-default'} <= set(h.lower() for h in hreflangs)):
        issues.append(f'Missing es-pe/x-default hreflang: {hreflangs}')

    robots_vals = re.findall(r'<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']*)["\']', raw, re.I)
    robots_str = ' '.join(robots_vals).lower()
    if expect_noindex:
        if 'noindex' not in robots_str or 'follow' not in robots_str:
            issues.append(f'Expected noindex,follow in robots meta, got {robots_vals}')
    else:
        if 'noindex' in robots_str:
            issues.append(f'Unexpected noindex on indexable page: {robots_vals}')

    og_props = re.findall(r'<meta[^>]+property=["\'](og:[^"\']+)["\'][^>]+content=["\']([^"\']*)["\']', raw, re.I)
    og_map = {k.lower(): v for k, v in og_props}
    req_ogs = ('og:type', 'og:site_name', 'og:locale', 'og:title', 'og:description', 'og:image', 'og:image:width', 'og:image:height', 'og:image:alt')
    if expect_status == 200:
        req_ogs = req_ogs + ('og:url',)
    for req_og in req_ogs:
        if not og_map.get(req_og):
            issues.append(f'Missing {req_og}')

    tw_names = re.findall(r'<meta[^>]+name=["\'](twitter:[^"\']+)["\'][^>]+content=["\']([^"\']*)["\']', raw, re.I)
    tw_map = {k.lower(): v for k, v in tw_names}
    for req_tw in ('twitter:card', 'twitter:title', 'twitter:description', 'twitter:image'):
        if not tw_map.get(req_tw):
            issues.append(f'Missing {req_tw}')

    h1s = [html.unescape(re.sub(r'<[^>]+>', '', h)).strip() for h in re.findall(r'<h1\b[^>]*>(.*?)</h1>', raw, re.S | re.I)]
    if len(h1s) != 1 or not h1s[0]:
        issues.append(f'Expected exactly 1 non-empty H1, found {len(h1s)}: {h1s}')

    ld_blocks = re.findall(r'<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>', raw, re.S | re.I)
    if len(ld_blocks) != 1:
        issues.append(f'Expected 1 JSON-LD block, found {len(ld_blocks)}')
    ld_types = []
    has_agg_rating = False
    for blk in ld_blocks:
        try:
            obj = json.loads(blk)
            graph = obj.get('@graph', [obj])
            for node in graph:
                if isinstance(node, dict) and '@type' in node:
                    t = node['@type']
                    ld_types.extend(t if isinstance(t, list) else [t])
                    if 'aggregateRating' in node or 'review' in node:
                        has_agg_rating = True
        except Exception as exc:
            issues.append(f'Invalid JSON-LD: {exc}')
    if expected_types:
        missing_t = [t for t in expected_types if t not in ld_types]
        if missing_t:
            issues.append(f'Missing JSON-LD types {missing_t}; got {ld_types}')
    if has_agg_rating:
        issues.append('Found aggregateRating/review in JSON-LD despite 0 real (non-_mk_demo) reviews')

    imgs = re.findall(r'<img\b[^>]*>', raw, re.I)
    bad_imgs = []
    for img in imgs:
        m = re.search(r'\balt\s*=\s*(["\'])(.*?)\1', img, re.I | re.S)
        if not m or not m.group(2).strip():
            bad_imgs.append(img[:120])
    if bad_imgs:
        issues.append(f'Images missing non-empty alt ({len(bad_imgs)}): {bad_imgs[:3]}')

    if check_ga_debug:
        u_dbg = url + ('&' if '?' in url else '?') + 'mk_ga_debug=1'
        st_d, _, body_d, _ = fetch(u_dbg)
        if st_d != expect_status or 'googletagmanager.com/gtag/js?id=G-EB0TZFNFK0' not in body_d or 'debug_mode:true' not in body_d:
            issues.append('GA4 debug snippet missing with ?mk_ga_debug=1')
    else:
        if 'G-EB0TZFNFK0' not in raw:
            issues.append('GA4 ID G-EB0TZFNFK0 not referenced in local commented snippet')

    if 'theme-color' not in raw:
        issues.append('Missing theme-color meta')
    if 'wp-generator' in raw.lower() or 'name="generator"' in raw.lower() or "name='generator'" in raw.lower():
        issues.append('Found generator meta tag')

    return {
        'url': url,
        'final_url': final_url,
        'status': status,
        'time_s': round(elapsed, 4),
        'bytes': len(raw.encode('utf-8')),
        'title': title,
        'title_len': len(title),
        'desc_len': len(desc),
        'canonical': canonicals[0] if canonicals else '',
        'robots': robots_vals,
        'h1s': h1s,
        'json_ld_types': ld_types,
        'img_total': len(imgs),
        'img_bad_alt': len(bad_imgs),
        'ok': len(issues) == 0,
        'issues': issues,
    }


def main():
    report = {'pages': {}, 'aux': {}, 'ok': True}

    pages_spec = [
        ('portada', f'{BASE}/', False, ['Organization', 'WebSite', 'Store'], 200, True),
        ('tienda', f'{BASE}/tienda/', False, ['Organization', 'WebSite', 'CollectionPage', 'ItemList'], 200, False),
        ('categoria_peluches', f'{BASE}/product-category/peluches/', False, ['Organization', 'WebSite', 'CollectionPage', 'ItemList', 'BreadcrumbList'], 200, False),
        ('ficha_abaco', f'{BASE}/product/abaco-pedagogico-y-laberinto-de-madera/', False, ['Organization', 'WebSite', 'Product', 'BreadcrumbList'], 200, True),
        ('ficha_auto_oferta', f'{BASE}/product/auto-con-control-remoto/', False, ['Organization', 'WebSite', 'Product', 'BreadcrumbList'], 200, False),
        ('ficha_tren_largo', f'{BASE}/product/tren-expreso-de-madera-con-vagones-magneticos/', False, ['Organization', 'WebSite', 'Product', 'BreadcrumbList'], 200, False),
        ('carrito', f'{BASE}/cart/', True, ['Organization', 'WebSite'], 200, False),
    ]

    for name, url, noidx, types, st, dbg in pages_spec:
        res = check_page(name, url, expect_noindex=noidx, expected_types=types, expect_status=st, check_ga_debug=dbg)
        report['pages'][name] = res
        if not res['ok']:
            report['ok'] = False

    # Add 1 item in isolated session to verify Checkout directly
    add_cart_item_for_checkout(474)
    res_chk = check_page('checkout', f'{BASE}/checkout/', expect_noindex=True, expected_types=['Organization', 'WebSite'], expect_status=200, check_ga_debug=True)
    report['pages']['checkout'] = res_chk
    if not res_chk['ok']:
        report['ok'] = False
    cleanup_cart()

    # Check 404, robots.txt, wp-sitemap.xml, and favicon.ico
    res_404 = check_page('not_found', f'{BASE}/pagina-inexistente-v19-check/', expect_noindex=True, expected_types=['Organization', 'WebSite'], expect_status=404, check_ga_debug=False)
    report['pages']['not_found'] = res_404
    if not res_404['ok']:
        report['ok'] = False

    st_r, _, body_r, _ = fetch(f'{BASE}/robots.txt')
    robots_ok = st_r == 200 and 'Disallow: /cart/' in body_r and 'Disallow: /checkout/' in body_r and 'Sitemap:' in body_r
    report['aux']['robots_txt'] = {'status': st_r, 'ok': robots_ok, 'preview': body_r[:400]}
    if not robots_ok:
        report['ok'] = False

    st_s, _, body_s, _ = fetch(f'{BASE}/wp-sitemap.xml')
    st_sp, _, body_sp, _ = fetch(f'{BASE}/wp-sitemap-posts-page-1.xml')
    st_sc, _, body_sc, _ = fetch(f'{BASE}/wp-sitemap-taxonomies-product_cat-1.xml')
    sitemap_ok = (
        st_s == 200
        and 'wp-sitemap-posts-product-1.xml' in body_s
        and 'wp-sitemap-taxonomies-product_cat-1.xml' in body_s
        and '/cart/' not in body_sp
        and '/checkout/' not in body_sp
        and '/my-account/' not in body_sp
        and '<lastmod>' in body_sc
    )
    report['aux']['sitemap'] = {'status': st_s, 'ok': sitemap_ok}
    if not sitemap_ok:
        report['ok'] = False

    st_f, url_f, _, _ = fetch(f'{BASE}/favicon.ico')
    fav_ok = st_f == 200 and ('png' in url_f or 'ico' in url_f)
    report['aux']['favicon'] = {'status': st_f, 'final_url': url_f, 'ok': fav_ok}
    if not fav_ok:
        report['ok'] = False

    OUT.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf8')
    print(json.dumps({
        'ok': report['ok'],
        'pages': {k: {'status': v['status'], 'ok': v['ok'], 'issues': v['issues'], 'time_s': v['time_s'], 'bytes': v['bytes']} for k, v in report['pages'].items()},
        'aux': {k: {'ok': v['ok'], 'status': v['status']} for k, v in report['aux'].items()},
    }, ensure_ascii=False, indent=2))
    if not report['ok']:
        raise SystemExit(1)


if __name__ == '__main__':
    main()
