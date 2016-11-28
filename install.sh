#!/bin/bash

echo "Building..."
docker-compose build

echo "Directories..."
export ROOT_DIR=$PWD
export SRC_DIR=$ROOT_DIR"/src"
echo $SRC_DIR
mkdir $SRC_DIR/{cache,logs}
chmod -R 777 $SRC_DIR/{cache,logs}
mkdir -p $SRC_DIR/public/elements/aptitus/{img,notas,logos,cvs}
chmod -R 777 $SRC_DIR/public/elements/aptitus/{img,notas,logos,cvs}

echo "Framework.."
export ZEND="ZendFramework-1.12.15"
curl https://packages.zendframework.com/releases/$ZEND/$ZEND-minimal.tar.gz | tar -xz
mv $ZEND-minimal/library/Zend $SRC_DIR/library/
rm -rf $ZEND-minimal

echo "Composer.."
cd $SRC_DIR && php composer.phar install

echo "Hosts.."
sudo su -c "echo '127.0.0.1 local.aptitus.com' >> /etc/hosts"
sudo su -c "echo '127.0.0.1 local.cdn.aptitus.com' >> /etc/hosts"

echo "Up..."
cd $ROOT_DIR
cp src/application/configs/docker.php.bkp src/application/configs/docker.php
cp src/application/configs/private.ini.bkp src/application/configs/private.ini
docker-compose up
