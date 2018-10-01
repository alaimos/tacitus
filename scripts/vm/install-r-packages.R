#!/usr/bin/env Rscript

source("https://bioconductor.org/biocLite.R")

biocLite(ask=FALSE)

biocLite(c("getopt", "RCurl", "rjson"),ask=FALSE)

# biocLite("inSilicoDb",ask=FALSE)

# biocLite("inSilicoMerging",ask=FALSE)

install.packages("/vagrant/scripts/vm/inSilicoDb_2.4.1.tar.gz", 
                 repos = NULL, type="source")

install.packages("/vagrant/scripts/vm/inSilicoMerging_1.15.9999.tar.gz", 
                 repos = NULL, type="source")
