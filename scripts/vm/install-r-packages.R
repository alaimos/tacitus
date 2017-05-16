#!/usr/bin/env Rscript

source("https://bioconductor.org/biocLite.R")

biocLite(ask=FALSE)

biocLite("getopt",ask=FALSE)

biocLite("rjson",ask=FALSE)

# biocLite("inSilicoDb",ask=FALSE)

# biocLite("inSilicoMerging",ask=FALSE)

install.packages("/vagrant/scripts/vm/inSilicoMerging_1.15.9999.tar.gz", 
                 repos = NULL, type="source")
