#!/bin/sh
. /etc/rc.subr
. /etc/util.subr
. /etc/configxml.subr

exerr () { echo -e "$*" >&2 ; exit 1; }
#
START_FOLDER=`pwd`

# Store the script's current location in a file
echo $START_FOLDER > /tmp/dnsmasqinstaller
# Check, if use fresh install or upgrade 
_rootfolder=`configxml_get "//dnsmasq/rootfolder"`
if [ ! -z $_rootfolder ]; then
	DMAS_ROOT=$_rootfolder
else
# use first argument as path to root folder
	DMAS_ROOT=$1
	if [ ! -d $DMAS_ROOT ]; then
        echo "Attempting to create a new destination directory....."
        mkdir -p $DMAS_ROOT || exerr "ERROR: Could not create directory!"
    fi
fi
# create working folder	
mkdir -p $START_FOLDER/temporary || exerr "ERROR: Could not create install directory!"

echo $DMAS_ROOT > /tmp/dnsmasq.tmp
cd $START_FOLDER/temporary || exerr "ERROR: Could not access install directory!"

# Fetch the master branch as a zip file
echo "Retrieving the most recent version of dnsmasq"
fetch https://github.com/alexey1234/nas4free-dnsmasq/archive/master.zip || exerr "ERROR: Could not write to install directory!"


# Extract the files we want, stripping the leading directory, and exclude
# the git nonsense
echo "Unpacking the tarball..."
tar -xvf master.zip --exclude='.git*' --strip-components 1
# Get rid of the tarball
rm master.zip
echo "dnsmasq install/update"
if [ `uname -p` = "amd64" ]; then
	echo "Renaming 64 bit ftp binary"
	mv sbin/dnsmasq_64 sbin/dnsmasq
	rm sbin/dnsmasq_86
else
	echo "Renaming 32 bit ftp binary"
	mv sbin/dnsmasq_86 sbin/dnsmasq
	rm sbin/dnsmasq_64
fi
cp -r * $DMAS_ROOT/
mkdir -p /usr/local/www/ext/dnsmasq
ln -s $DMAS_ROOT/www /usr/local/www/ext/dnsmasq
cd /usr/local/www
# For each of the php files in the extensions folder
for file in /usr/local/www/ext/dnsmasq/*.php
do
# Check if the link is already there
	if [ -e "${file##*/}" ]; then
		rm "${file##*/}"
	fi
		# Create link
	ln -s "$file" "${file##*/}"
done
echo "Congratulations! dnsmasq was updated/installed . Navigate to config web page and push Save "

# Clean after work
cd $START_FOLDER
# Get rid of staged updates
rm -Rf temporary/*
rmdir temporary
rm /tmp/dnsmasqinstaller
rm /tmp/dnsmasqversion
currentdate=`date -j +"%h %d %H:%M:%S"`
echo "$currentdate installer[0001]: install or upgrade action successfull" >> /var/log/dnsmasq.log
