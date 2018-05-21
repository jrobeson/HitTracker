import * as path from 'path';
import * as gulp from 'gulp';
import * as jetpack from 'fs-jetpack';
import * as download from 'download';
import * as tmp from 'tmp';
import * as util from 'util';

// @todo get the php version from composer.json
const phpVersion = '7.2.5';

const fetchPhp = async (unpackDir: string, platform: string, arch: string) => {
    /*if (platform !== 'win32') {
        console.log(`Not Downloading PHP for ${platform}`)
        return;
    }*/
    // @todo: don't use http url for getting php
    const tmpObject = tmp.dirSync();

    const phpArch = arch === 'ia32' ? 'x86' : arch;
    const url = `https://github.com/lazerball/win-php/releases/download/0.0.1/win-php-${phpArch}-${phpVersion}.tar.gz`;
    console.log('Downloading PHP');
    try {
        await download(url, tmpObject.name, { extract: true });
    } catch(e) {
        console.log(e.message);
        return;
    }
    console.log('Copying PHP files');
    jetpack.copy(path.join(`${tmpObject.name}/php-${phpArch}`), unpackDir, {overwrite: true});

    jetpack.remove(tmpObject.name);
    console.log('Successfully downloaded PHP');
};


const fetchCaddy = async (unpackDir: string, platform: string, arch: string) => {
    if (jetpack.exists(unpackDir)) {
        return;
    }

    const caddyOs = platform === 'win32' ? 'windows' : platform;

    const caddyArchMap = <any> {
        ia32: '386',
        x64: 'amd64',
        arm: 'arm',
        arm64: 'arm64',
    };
    let caddyArch = caddyArchMap[arch];
    let caddyArm = '';
    if (caddyArch === 'arm') {
        process.config.variables.hasOwnProperty('arm_version');
        caddyArm = (process.config.variables as any).arm_version;
    }
    caddyArch = `${caddyArch}${caddyArm}`;
    const caddyFeatures = [
        'http.cgi',
        'http.cors',
        'http.expires',
        'http.realip',
        'http.upload',
    ].join(',');

    const url = `https://caddyserver.com/download/${caddyOs}/${caddyArch}?plugins=${caddyFeatures}`;

    try {
        await download(url, unpackDir, {extract: true});
        ['init', 'CHANGES.txt', 'README.txt'].forEach((file) => {
            jetpack.remove(path.join(unpackDir, file));
        });
        console.log('Sucessfully downloaded caddy');
    } catch (e) {
        console.log(e.message);

    }
};

gulp.task('bundle-third-party', async () => {
    const baseUnpackDir = 'bundled';
    const arch = process.arch;
    const platform = process.platform;

    return await Promise.all([
        fetchCaddy(path.join(baseUnpackDir, `caddy-${platform}-${arch}`), platform, arch),
        fetchPhp(path.join(baseUnpackDir, `php-${platform}-${arch}`), platform, arch),
    ]);
});
