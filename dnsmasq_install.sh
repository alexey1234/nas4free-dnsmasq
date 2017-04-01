#!/bin/sh
BRANCHENAME=working
exerr () { echo -e "$*" >&2 ; exit 1; }

#
START_FOLDER=`pwd`

# Store the script's current location in a file
echo $START_FOLDER > /tmp/dnsmasqinstaller

# This first checks to see that the user has supplied an argument
if [ ! -z $1 ]; then
    
    DMAS_ROOT=$1    
    
    if [ ! -d $DMAS_ROOT ]; then
        echo "Attempting to create a new destination directory....."
        mkdir -p $DMAS_ROOT || exerr "ERROR: Could not create directory!"
    fi
	
#    cd $DMAS_ROOT || exerr "ERROR: Could not access install directory!"
else
# We are here because the user did not specify an alternate location. Thus, we should use the 
# current directory as the root.
    DMAS_ROOT=$START_FOLDER
fi
mkdir -p temporary || exerr "ERROR: Could not create install directory!"
echo $DMAS_ROOT > /tmp/dnsmasq.tmp
cd temporary || exerr "ERROR: Could not access install directory!"

# Fetch the master branch as a zip file
echo "Retrieving the most recent version of dnsmasq"
fetch https://github.com/alexey1234/nas4free-dnsmasq/archive/${BRANCHENAME}.zip || exerr "ERROR: Could not write to install directory!"


# Extract the files we want, stripping the leading directory, and exclude
# the git nonsense
echo "Unpacking the tarball..."
tar -xvf ${BRANCHENAME}.zip --exclude='.git*' --strip-components 1
# Get rid of the tarball
rm ${BRANCHENAME}.zip

# Obtain binary from FreeBSD package
echo "Fetch package from FreeBSD..."
pkg fetch -y -o tmp dnsmasq
cd tmp/All
tar -xzf dnsmasq*.txz
cd ../..
rm -f sbin/dmsmasq_64
rm -f sbin/dmsmasq_86
cp tmp/All/usr/local/sbin/dnsmasq sbin/dnsmasq
rm -fr tmp


# Check, if use fresh install or upgrade 
_rootfolder=`configxml_get "//dnsmasq/rootfolder"`
if [ ! -z $_rootfolder ]; then
# Update current config
	DMAS_ROOT=$_rootfolder
	/etc/rc.d/dnsmasq onestop
	rm -f /usr/local/sbin/dnsmasq
	rm -f /etc/rc.d/dnsmasq
	cp -f -R $START_FOLDER/temporary/* $DMAS_ROOT/
	ln -s $DMAS_ROOT/sbin/dnsmasq /usr/local/sbin/dnsmasq
	ln -s $DMAS_ROOT/sbin/dnsmasq.d /etc/rc.d/dnsmasq
else
	echo "Look like fresh install"
	cp -f -R $START_FOLDER/temporary/* $DMAS_ROOT/
#create webroot files
	mkdir -p /usr/local/www/ext
	ln -s $DMAS_ROOT/dnsmasq /usr/local/www/ext/dnsmasq
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
fi
chmod 755 $DMAS_ROOT/sbin/*
echo "Congratulations! dnsmasq was updated/installed . Navigate to config web page and push Save "

# Clean after work
cd $START_FOLDER
# Get rid of staged updates
rm -Rf temporary/*
rmdir temporary
rm /tmp/dnsmasqinstaller
currentdate=`date -j +"%h %d %H:%M:%S"`
echo "$currentdate installer[0001]: install or upgrade action successfull" >> /var/log/dnsmasq.log