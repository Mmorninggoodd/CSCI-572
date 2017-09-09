#!/bin/bash

###################################################################

clear  # clear the terminal 

echo "Shreyansh Is in Action on Your Terminal..." 

###################################################################

# Path settings for Hadoop ###################################

export PATH=${JAVA_HOME}/bin:${PATH}
export HADOOP_CLASSPATH=${JAVA_HOME}/lib/tools.jar

hadoop fs -ls

#############################################################

# Jar Creation For Map Reduce ###########################

hadoop com.sun.tools.javac.Main $2.java
jar cf $3.jar $2*.class


hadoop fs -copyFromLocal ./$3.jar

hadoop fs -cp ./$3.jar gs://$1/JAR


################################################################

### Running Hadoop ############################################

hadoop jar $3.jar $2 gs://$1/$4 gs://$1/output

#############################################################

#### Merging the Output #################################

hadoop fs -getmerge gs://$1/output ./output.txt
hadoop fs -copyFromLocal ./output.txt
hadoop fs -cp ./output.txt gs://$1/output.txt


##############################################################

echo "\n\n\n Congratulations!!! Shreyansh Script has Finished!!!" 

##################################################################
