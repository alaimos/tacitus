#!/usr/bin/env Rscript
##########################################################################################################
# TACITuS - Integrator Script
# Developed by S. Alaimo (alaimos at dmi dot unict dot it)
##########################################################################################################
script.dir <- dirname((function() {
    cmdArgs <- commandArgs(trailingOnly = FALSE)
    needle <- "--file="
    match <- grep(needle, cmdArgs)
    if (length(match) > 0) {
        # Rscript
        return(normalizePath(sub(needle, "", cmdArgs[match])))
    } else {
        # 'source'd via R console
        return(normalizePath(sys.frames()[[1]]$ofile))
    }
})())
suppressWarnings(suppressPackageStartupMessages(library(getopt, quietly = TRUE)))
suppressWarnings(suppressPackageStartupMessages(library(rjson, quietly = TRUE)))
source(paste0(script.dir, "/common/integrator.common.R"))

cmd.line.valid.args <- matrix(c(
    "config",            "c",  1,  "character",  "A json file which lists all options",
    "status",            "s",  1,  "character",  "A json file where status will be written",
    "help",              "h",  0,  "logical",    "This help"
), ncol=5, byrow=TRUE)

write.status <- function (file, status) {
    output <- toJSON(list(
        ok=(is.logical(status) && status==TRUE),
        message=(ifelse(test=("error" %in% class(status)),yes=status$message,no=""))
    ))
    cat(output, file=file, fill=FALSE, append=FALSE)
}

main <- function (args) {
    config.file <- args$config
    config.data <- fromJSON(file = config.file)
    write.status(args$status, tryCatch({
        cat("Reading selections")
        selections <- lapply(1:length(config.data$selections), function (i, s) {
            se <- read.selection(s[[i]]$data, s[[i]]$metatada, na.strings=config.data$na.strings)
            cat("...",i,sep="")
            return (se)
        }, config.data$selections)
        cat("...OK\n")
        cat("Preparing selections for integration")
        selections <- prepare.selections(selections)
        cat("...OK\n")
        cat("Integrating metadata matrices")
        int.meta   <- merge.metadata(selections)
        cat("...OK\n")
        cat("Writing integrated metadata matrix")
        write.table(int.meta, file=config.data$output$metadata, append = FALSE, sep="\t", 
                    row.names = FALSE, col.names = TRUE)
        rm(int.meta)
        cat("...OK\n")
        cat("Integrating data matrices")
        int.data   <- merge.data(selections, method=config.data$method, digits=config.data$digits)
        cat("...OK\n")
        cat("Writing integrated data matrix")
        write.table(int.data, file=config.data$output$data, append = FALSE, sep="\t", 
                    row.names = FALSE, col.names = FALSE)
        rm(int.data)
        cat("...OK\n")
        TRUE
    }, error=function(e) {
        return (e)
    }))
}

opt <- getopt(cmd.line.valid.args)

if (!is.null(opt$help) || is.null(opt$config) || is.null(opt$status)) {
    cat(paste(getopt(cmd.line.valid.args, usage=TRUE), "\n"))
} else {
    suppressMessages(suppressWarnings(main(opt)))
}

