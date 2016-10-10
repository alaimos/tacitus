library(inSilicoMerging)
#######################################################################################################################
# Read Selection Data and Metadata
#######################################################################################################################
# Parameters:
#  - data.file    : character(1)  the path of the data file
#  - metadata.file: character(1)  the path of the metadata file
#######################################################################################################################
# Returns a "selection" list: a list which contains all data and metadata matrices
#######################################################################################################################
read.selection <- function (data.file, metadata.file) {
  
  data <- read.delim(file=data.file, header=FALSE, stringsAsFactors=FALSE)
  if (nrow(data) <= 1 || ncol(data) <= 1) {
    stop("Invalid data file: it should contain at least 1 sample and 1 probe.")
  }
  metadata <- read.delim(file=metadata.file, header=TRUE, stringsAsFactors=FALSE, check.names=FALSE)
  if (nrow(metadata) != (ncol(data)-1)) {
    stop("Invalid metadata file: it should contain the same number of samples as the data one.")
  }
  if (ncol(metadata) < 1) {
    stop("Invalid metadata file: no columns found.")
  }
  tmp <- data.matrix(data[-1,-1])
  rownames(tmp) <- NULL
  colnames(tmp) <- NULL
  selection <- list(
    data=list(
      expression.matrix=tmp,
      probes=data[,1][-1],
      samples=as.character(data[1,])[-1]
    ),
    metadata=metadata
  )
  class(selection) <- "selection"
  return (selection)
}

#######################################################################################################################
# Prepares a set of selections for integration
#######################################################################################################################
# Parameters:
#  - ...: multiple "selection" lists
#######################################################################################################################
# Returns a "prepared.selections" list
#######################################################################################################################
prepare.selections <- function (...) {
  selections <- list(...)
  if (length(selections) < 1) {
    stop("You must specify at least one selection")
  }
  if (length(selections) != sum(sapply(selections, function (x) (class(x) == "selection")))) {
    stop("All specified parameters must be a \"selection\" list.")
  }
  common.probes <- Reduce(intersect, lapply(selections, function (x) (x$data$probes) ))
  if (length(common.probes) < 1) {
    stop("No common probes found between selections to integrate")
  }
  selected.probes <- lapply(selections, function (x, c) (which(x$data$probes %in% c)) , common.probes)
  expression.matrices <- lapply(selections, function (x, c) (ExpressionSet(assayData=x$data$expression.matrix[which(x$data$probes %in% c),])) , common.probes)
  all.samples <- Reduce(c, lapply(selections, function (x) (x$data$samples) ))
  metadata.matrices <- lapply(selections, function (x) (x$metadata) )
  result <- list(
    expression.matrices=expression.matrices,
    metadata.matrices=metadata.matrices,
    all.samples=all.samples,
    all.probes =common.probes,
    selected.probes=selected.probes
  )
  class(result) <- "prepared.selections"
  return (result)
}

#######################################################################################################################
# Merge all metadata matrices
#######################################################################################################################
# Parameters:
#  - selection: list(1) of class prepared.selections  a set of selections to merge
#######################################################################################################################
# Returns a data frame with the merged metadata
#######################################################################################################################
merge.metadata <- function (selection) {
    matrices <- selection$metadata.matrices
    all.metas <- Reduce(union, lapply(matrices, function (x) (colnames(x))))
    n.samples <- sum(sapply(matrices, nrow))
    values <- lapply(all.metas, function (col) {
        Reduce(c, lapply(matrices, function (x, col) {
            if (col %in% colnames(x)) {
                return (x[,col])
            } else {
                return (rep(NA, nrow(x)))
            }
        }, col))
    })
    names(values) <- all.metas
    df.merged <- data.frame(values, check.names=FALSE)
    return (df.merged)
}

#######################################################################################################################
# Merge all data matrices
#######################################################################################################################
# Parameters:
#  - selection: list(1) of class prepared.selections  a set of selections to merge
#######################################################################################################################
# Returns a matrix with the merged data
#######################################################################################################################
merge.data <- function (selection, method="NONE", digits=getOption("digits")) {
    merged.eset <- merge(selection$expression.matrices, method=method)
    merged.mtx  <- format(exprs(merged.eset), digits=digits, scientific=FALSE)
    final.mtx   <- matrix(data = NA, nrow=(nrow(merged.mtx)+1), ncol=(ncol(merged.mtx)+1))
    final.mtx[1,1] <- "Probe"
    final.mtx[2:nrow(final.mtx),1] <- selection$all.probes
    final.mtx[1,2:ncol(final.mtx)] <- selection$all.samples
    final.mtx[2:nrow(final.mtx),2:ncol(final.mtx)] <- merged.mtx
    return (final.mtx)
}

