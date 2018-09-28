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

clean.factor <- function (f) {
    f <- gsub("^\\s+|\\s+$", "", f, perl = TRUE)
    f[f == ""] <- NA
    return (f)
}

main <- function (args) {
    config.file <- args$config
    config.data <- fromJSON(file = config.file)
    write.status(args$status, tryCatch({
        in.data  <- config.data$input_data
        in.meta  <- config.data$input_meta
        out.data <- config.data$output_data
        out.meta <- config.data$output_meta
        cat("Reading dataset")
        if (!file.exists(in.data)) {
            stop("Input data file does not exist.")
        }
        if (!file.exists(in.meta)) {
            stop("Input metadata file does not exist.")
        }
        data <- read.table(in.data, header = TRUE, sep="\t", check.names = FALSE, 
                           stringsAsFactors = FALSE)
        meta <- read.table(in.meta, header = TRUE, sep="\t", check.names = FALSE, 
                           stringsAsFactors = FALSE, na.strings = c("","NA"))
        cat("...OK\n")
        cat("Preparing data for Galaxy")
        dt  <- data.matrix(data[,-1])
        tmp <- tapply(1:nrow(dt), data[,1], function (x) {
            if (length(x) == 1) { return (dt[x,,drop=FALSE])}
            return (colMeans(dt[x,,drop=FALSE]))
        })
        dt  <- do.call(rbind, tmp)
        tmp <- data.frame(x=names(tmp))
        colnames(tmp) <- colnames(data)[1]
        df  <- cbind(tmp, dt)
        rownames(df)  <- NULL
        rm(dt, tmp)
        samples <- colnames(df)[-1]
        rownames(meta) <- meta[,1]
        meta    <- meta[samples,]
        for (c in colnames(meta)[-1]) {
            meta[[c]] <- gsub(".","_", make.names(clean.factor(meta[[c]])), fixed = TRUE)
            meta[[c]][meta[[c]] %in% c("X_","NA_","X__")] <- NA
        }
        rownames(meta) <- NULL
        cat("...OK\n")
        cat("Writing data")
        write.table(df,   file=out.data, append = FALSE, quote = FALSE, sep="\t", na = "", 
                    row.names = FALSE, col.names = TRUE)
        write.table(meta, file=out.meta, append = FALSE, quote = FALSE, sep="\t", na = "", 
                    row.names = FALSE, col.names = TRUE)
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

